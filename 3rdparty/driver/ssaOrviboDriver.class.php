<?php



class ssaOrviboDriver
{   private  $sock;
    private $localIP ; 
	private $broadcastip ; 
	private $port ;
	private $orbivoIp ;
	private $twenties=array(0x20, 0x20, 0x20, 0x20, 0x20, 0x20);
 
 	// singleton instance 
  	private static $instance; 
  	// getInstance method 
  	public static function getInstance() { 
	    if(!self::$instance) { 
    	  self::$instance = new self(); 
    	} 
	    return self::$instance; 

  	} 	

 	/**
 	* Get orbivoIp
 	* @return  
 	*/
 	public function getOrbivoIp()
 	{
 	    return $this->orbivoIp;
 	}
 	
 	/**
 	* Set orbivoIp
 	* @return $this
 	*/
 	public function setOrbivoIp($orbivoIp)
 	{
 	    $this->orbivoIp = $orbivoIp;
 	    return $this;
 	}
    

    function __construct() 
    {
       
    } 








	/**
	* Get port
	* @return  
	*/
	public function getPort()
	{
	    return $this->port;
	}

	/**
	* Set port
	* @return $this
	*/
	public function setPort($port)
	{
	    $this->port = $port;
	    return $this;
	}

	/**
	* Get localIP
	* @return  
	*/
	public function getLocalIP()
	{
	    return $this->localIP;
	}

	/**
	* Set localIP
	* @return $this
	*/
	public function setLocalIP($localIP)
	{
	    $this->localIP = $localIP;
	    return $this;
	}

    function __destruct()
    {
        socket_close($this->sock);
    }

	private function binaryToString($buf)
	{
   		$res='';
   		for($i=0;$i<strlen($buf);$i++)
   		{
    		$num=dechex(ord($buf[$i]));
    		if (strlen($num)==1) 
    		{
     			$num='0'.$num;
    		}
    		$res.=$num;
   		}
   		return $res;
  	}

	private function HexStringToArray($buf) 
	{
   		$res=array();
   		for($i=0;$i<strlen($buf)-1;$i+=2) 
   		{
    		$res[]=(hexdec($buf[$i].$buf[$i+1]));
   		}
   		return $res;   
  	}

  	private function makePayload($data) 
  	{
  		$res='';
  		foreach($data as $v) 
  		{
   			$res.=chr($v);
  		}
  		return $res;
 	}


 	function createUdpSocket()
 	{   
 		if(!($this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)))
		{
	    	$errorcode = socket_last_error();
	    	$errormsg = socket_strerror($errorcode);
	        throw new Exception("Couldn't create socket: [$errorcode] $errormsg \n");
		    
		}
		// Bind the source address
  		if( !socket_bind($this->sock, '0.0.0.0' , $this->port) )
  		{   
    		$errorcode = socket_last_error();
    		$errormsg = socket_strerror($errorcode);
     
    		throw new Exception("Could not bind socket : [$errorcode] $errormsg \n");
  		}

  		socket_set_option($this->sock, SOL_SOCKET, SO_BROADCAST, 1);

 	}
    
    private function crop($chaine,$length)
    { return substr($chaine,-strlen($chaine)+$length);

    }

    private function decodeDiscover($chaine)
    {   /*
		68 64 = Magic Word
		00 29 = Packet length (41 bytes)
		71 61 = Command ID
		ac cf 23 2a 5f fa = MAC address
		20 20 20 20 20 20 = MAC address padding (to bring the total up to 12 bytes)
		fa 5f 2a 23 cf ac = MAC address, little endian
		20 20 20 20 20 20 = MAC address padding, little endian
		49 52 44 30 30 35 = The string "IRD005" in hex (hardware identifier, perhaps?)
		b5 8a 94 = Time since manufacture (?)
		d7 = ??


    	*/
    	$rep['magicWord']=substr($chaine, 0, 4);
    	$chaine=$this->crop($chaine,4);
    	
    	$rep['packetLenght']=substr($chaine, 0, 4);
    	$chaine=$this->crop($chaine,4);
    	
    	$rep['cmdId']=substr($chaine, 0, 6);
    	$chaine=$this->crop($chaine,6);
    	
    	$rep['mac']=substr($chaine, strpos($chaine, 'accf'),12);
        

 	return $rep;



    }

 	function discover()
	{    
                $listAllOne=array();
		$payload = $this->makePayload(array(0x68, 0x64, 0x00, 0x06, 0x71, 0x61));
		
                $client= socket_sendto($this->sock, $payload, strlen($payload), 0, $this->orbivoIp, $this->port);
                if (!$client)
                {
                    $errorcode = socket_last_error();
                    $errormsg = socket_strerror($errorcode);

                    throw new Exception("Could not bind socket : [$errorcode] $errormsg \n");
                    
                }
		$code=$this->receiveMessage('discover');
   		return $code;
           
   	}

   	function emitIr($mac,$msg)
   	{
   		$this->subscribe($mac);

   		$code=trim(str_replace(' ', '', $msg));
 		$len1=(int)(strlen($code)/2)+26;
 		$high_byte=floor($len1/256);
 		$low_byte=$len1-$high_byte*256;
 		$h1=dechex($high_byte);
 		$l1=dechex($low_byte);
 		if (strlen($h1)<2) {
  			$h1='0'.$h1;
 		}
 		if (strlen($l1)<2) {
  			$l1='0'.$l1;
 		}
 		
 		$packetlen=$h1.$l1;
		$len2=strlen($code)/2;
 		$high_byte=floor($len2/256);
 		$low_byte=$len2-$high_byte*256;
 		$h2=dechex($high_byte);
 		$l2=dechex($low_byte);
 		if (strlen($h2)<2) {
  			$h2='0'.$h2;
 		}
 		if (strlen($l2)<2) {
  			$l2='0'.$l2;
 		}

 		$irlen=array_reverse($this->HexStringToArray($h2.$l2));
   		$randomBitA = rand(0, 255);
 		$randomBitB = rand(0, 255);


 		/*
 		68 64 = Magic Word
		01 9E = Length of packet (414 bytes)
		69 63 = Command ID (blast IR)
		AC CF 23 2A 5F FA = MAC Address
		20 20 20 20 20 20 = MAC Address padding
		65 00 00 00 = Unknown
		73 8C = Two randomness bytes. AO refuses to blast twice if these bytes are the same as before, so set this to something random
		84 01 = Length of IR (little endian?)
		00 00 ... 1E 02 00 00 = The raw IR data
		*/

   		$payload  = $this->makePayload(array(0x68, 0x64));
  		$payload .= $this->makePayload($this->HexStringToArray($packetlen));
  		$payload .= $this->makePayload(array(0x69, 0x63));
  		$payload .= $this->makePayload($this->HexStringToArray($mac));
  		$payload .= $this->makePayload($this->twenties);
  		$payload .= $this->makePayload(array(0x65, 0x00, 0x00, 0x00));
  		$payload .= $this->makePayload(array($randomBitA, $randomBitB));
  		$payload .= $this->makePayload($irlen);
  		$payload .= $this->makePayload($this->HexStringToArray($code));

  		//var_dump($this->binaryToString($payload));

  		socket_sendto($this->sock, $payload, strlen($payload), 0, $this->orbivoIp, $this->port);
   	}
    

    private function subscribe($mac)
    {	
	$macReversed=array_reverse($this->HexStringToArray($mac));
     
       	$payload  = $this->makePayload(array(0x68, 0x64, 0x00, 0x1e, 0x63, 0x6c));
       	$payload .= $this->makePayload($this->HexStringToArray($mac));
       	$payload .= $this->makePayload($this->twenties);
       	$payload .= $this->makePayload($macReversed);
       	$payload .= $this->makePayload($this->twenties);

       	$client=socket_sendto($this->sock, $payload, strlen($payload), 0, $this->orbivoIp, $this->port);
	if (!$client)
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new Exception("[subscribe] Could not bind socket : [$errorcode] $errormsg \n");
                   
        }	


    }
    
    function learningIr($mac)
    {
       try {
        
           $this->subscribe($mac);
       }
       catch (Exception $e)
       {   
            throw new Exception($e->getMessage());
       }
		

		/*
		 
		Send: 68 64 00 18 6c 73 AC CF 23 2A 5F FA 20 20 20 20 20 20 01 00 00 00 00 00
		 
		68 64 = Magic Word
		00 18 = Packet length (41 bytes)
		6C 73 = Command ID (Learning mode)
		ac cf 23 2a 5f fa = MAC address
		20 20 20 20 20 20 = MAC address padding (to bring the total up to 12 bytes)
		01 00 00 00 00 00 = Unknown

		*/ 

        $payload   = $this->makePayload(array(0x68, 0x64, 0x00, 0x18, 0x6c, 0x73));
        $payload  .= $this->makePayload($this->HexStringToArray($mac));
        $payload  .= $this->makePayload($this->twenties);
        $payload  .= $this->makePayload(array(0x01, 0x00, 0x00, 0x00, 0x00, 0x00));
  		
  	
        $client=socket_sendto($this->sock, $payload, strlen($payload), 0, $this->orbivoIp, $this->port);
	if (!$client)
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new Exception("[learnIr] Could not bind socket : [$errorcode] $errormsg \n");
                   
        }
        
        
        
	$code=$this->receiveMessage('learning');
	return $code;

		
   }


   	/**
 	* @param string $expected
 	* 	learning
 	*/
   	private function receiveMessage($expected)
   	{
   		//Receive some data
    	socket_set_option($this->sock,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>10,"usec"=>0));
    	$end_time = time() + 2;
    	
		while ($end_time > time()) {
    		socket_recvfrom($this->sock, $buffer, 512, 0, $this->orbivoIp, $this->port);
    		$message=$this->binaryToString($buffer);

    		
    		$command=substr($message,8,4);
    		
    		switch ($command)
     		{
        		case '7161':
        			if ($expected==="discover")
          			{	//echo "<br> discover :$message";
          				return $this->decodeDiscover($message) ;
          			}
          			break;
          		case '6963':
          			if ($expected==="emit")
          			{	echo "<br> emit :$message";
          			}
          			break;
          		case '7274':
          			if ($expected==="name")
          			{	echo "<br> name :$message";
          			}
          			break;
          		case '636c':
          			if ($expected==="suscribe")
          			{	echo "<br> suscribe :$message";
          			}
          			break;
          		case '6463':
          			if ($expected==="state")
          			{	echo "<br> state :$message";
          			}
          			break;
          		case '6469':
          			if ($expected==="ping")
          			{	echo "<br> ping :$message";
          			}
          			break;
          		case '7366':
          			if ($expected==="change")
          			{	echo "<br> change :$message";
          			}
          			break;
          		case '6c73':
          			if ($expected==="learning")
          			{	if (substr($message,4,4) != '0018')
          				{	//echo "<br> learning : $message";
          					return substr($message, 52);
          				}
          			}
          			break;
        		default:
        		    echo "<br> commande inconnu";
        		    return "";
		    }


    		
    		
    		
    		// sleep 500ms to decrease cpu usage
    		usleep(500000);
		}
		return "0";


   	}



}




 


  


 


 











?>