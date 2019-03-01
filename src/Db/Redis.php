<?php namespace Chukdo\Db;

use Chukdo\Contracts\Db\Redis as RedisInterface;

/**
 * Gestion de la base de donnée NOSQL Redis basé sur son protocole unifié
 *
 * @package 	Exception
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Redis implements RedisInterface
{
    /**
     * Socket
     * 
     * @var resource
     */
    protected $sock = null;
	
    /**
     * Pointer SCAN pour iteration
     * 
     * @var int 
     */
	protected $pointer = 0;
	
    /**
     * Pile de stockage SCAN
     * 
     * @var array 
     */
	protected $stack = [];
    
    /**
     * Arguements par defaut pour le SCAN
     * 
     * @var array 
     */
	protected $args = [];
	
    /**
     * type de SCAN (SCAN, HSCAN, SSCAN)
     * 
     * @var string 
     */
	protected $type = null;	

    /**
     * Redis constructor.
     * @param string|null $dsn
     * @param int|null $timeout
     * @throws RedisException
     */
    public function __construct(string $dsn = null, int $timeout = null)
    {
        $dsn  = parse_url($dsn ?: 'redis://127.0.0.1:6379');
        $host = $dsn['host'];
        $port = $dsn['port'];

        $this->sock = fsockopen($host, $port, $errno, $errstr, $timeout ?: 5);

        if ($this->sock === null) {
            throw new RedisException("[$errno $errstr]");
        }
        
        if (isset($dsn['pass'])) {
            if(!$this->__call("AUTH", [$dsn['pass']])) {
                throw new RedisException("Wrong password");
            }
        }
		
		$this->setTypeIterator('scan');
    } 
	 
    /**
     * Arguments à ajouter à la commande SCAN lors d'une itération Redis 
	 * Ex. scan 0 MATCH *11*
     *
     * @param   array   $args
     * @return  void
     */
	public function setArgsIterator(array $args): void
	{
		$this->args = $args;
	}
	
    /**
     * Défini le type de commande SCAN lors d'une itération Redis 
	 * Ex. SCAN SSCAN HSCAN
     *
     * @param   string  $type
     * @return  void
     */
	public function setTypeIterator(string $type): void
	{
		$this->type = strtoupper($type);
	}

    /**
     * Ecris la commande SCAN lors d'une itération Redis
     * Ex. SCAN SSCAN HSCAN
     *
     * @param int $pointer
     * @return mixed
     * @throws RedisException
     */
	protected function getIterator(int $pointer)
	{
		$this->write($this->command(array_merge([$this->type, $pointer], $this->args)));
		
		return $this->read();
	}

    /**
     * Retourne le nombre d'enregistrement dans la base redis
     *
     * @return int|mixed
     * @throws RedisException
     */
	public function count()
	{
		$this->write($this->command(['dbsize']));
		
		return $this->read();
	}

    /**
     * Initialise l'iteration
     *
     * @throws RedisException
     */
	public function rewind()
	{
		/** Reset */
		$this->stack	=[];
		$this->pointer 	= 0;

		/** command SCAN */
		$scan = $this->getIterator($this->pointer);

		if (count($scan) == 2) {
			$this->pointer	= (int) $scan[0];
			$this->stack	= (array) $scan[1];
		}
	}

    /**
     * @return bool|mixed
     * @throws RedisException
     */
	public function current()
	{
        $current = false;

        if (isset($this->stack[0])) {
            $key = $this->stack[0];

            switch ($this->__call("TYPE", [$key])) {
                case 'string' :
                case 'set' :
                    $current = $this->get($key);
                    break;
                case 'list' :
                    $current = $this->__call("LRANGE", [$key, '0', '-1']);
                    break;
                case 'zset' :
                    $current = $this->__call("ZRANGE", [$key, '0', '-1']);
                    break;
                case 'hash' :
                    $current = $this->__call("HGETALL", [$key]);
                    break;
            }
        }

        return $current;
	}
    
    /**
     * Retourne la cle de l'element courant
     * 
     * @return string
     */
	public function key()
	{
		return isset($this->stack[0]) ? 
			$this->stack[0] : 
			'';
	}

    /**
     * Pointe sur l'element suivant
     *
     * @throws RedisException
     */
	public function next()
	{
		if (!empty($this->stack)) {
			array_shift($this->stack);
			
		} else if ($this->pointer !== 0) {
			
			/** command SCAN */
			$scan = $this->getIterator($this->pointer);
			
			if (count($scan) == 2) {
				$this->pointer	= (int) $scan[0];
				$this->stack	= (array) $scan[1];
			}
		}
	}
	
    /**
     * Verifie si il y a un element apres l'element courant
     * apres l'appel de rewind() ou next()
     * 
     * @return bool
     */
	public function valid(): bool
	{
		return $this->pointer === 0 && empty($this->stack) ? false : true;
	}

    /**
     * Détruit la connexion au serveur Redis
     */
    public function __destruct()
    {
        $this->sock = '';
    }
	
    /**
     * Lecture d'une reponse du serveur
     *
     * @throws  RedisException
     * @return 	mixed
     */ 
    public function read()
    {
        $get   = stream_get_line($this->sock, 512, "\r\n");
        $reply = substr($get, 1);

		if ($get === 0) {
			throw new RedisException('Failed to read type of response from stream');
		}
		
        switch (substr($get, 0, 1)) {
            
            /** Error */
            case '-':
                throw new RedisException($reply);
                break;
                
            /** Inline */
            case '+':
                $s = $reply;
                
                if ($s == 'OK') {
                    $s = true;
                }
                break;
                
            /** Integer */
            case ':':
                $s = intval($reply);
                break;
                  
            /** Bulk */
            case '$': 
                $s = null;
                
                if ($reply == '-1') {
                    break;
                }

                $size = intval($reply);
                $read = 0;

                if ($size > 0) {
                    while($read < $size) {
                        $len = min(1024, $size - $read);
                        $read += $len;

                        if (($r = stream_get_line($this->sock, $len)) !== 0) {
                            $s .= $r;
                        } else {
                            throw new RedisException('Failed to read response from stream');
                        }
                    }
                }
                
                /** \r\n */
                stream_get_line($this->sock, 2);
                break;
                
            /** Multi Bulk */
            case '*':
                $s = null;
                
                if ($reply == '*-1') {
                    break;
                }

                $c = intval($reply);
                $s =[];
                
                for ($i = 0; $i < $c; $i++) {
                    $s[] = $this->read();
                }
                break;
            default:
                throw new RedisException("Unknow response [$reply]");
                break;
        }

        return $s;
    }

    /**
     * Ecriture d'une commande basé sur le protocol unifié de Redis
	 * 
     * @param 	string 	$c command
     * @throws  RedisException
     * @return  void
     */ 
    public function write(string $c): void
    {
        for ($written = 0; $written < mb_strlen($c); $written += $fwrite) {
            $fwrite = fwrite($this->sock, mb_substr($c, $written));

            if ($fwrite === false || $fwrite <= 0) {
                throw new RedisException('Stream write error');
            }
        }
    }
	    
    /**
     * Formate une commande Redis (protocol unifié de Redis)
	 * 
     * @param 	array 	$args arguments
     * @return 	string
     */ 
	public function command(array $args): string
	{
        $c = '*'.count($args)."\r\n";
        
        foreach ($args as $arg) {
            $c .= '$'.mb_strlen($arg)."\r\n".$arg."\r\n";
        }
		
		return $c;
	}

    /**
     * Ecriture de commandes dans un pipeline (gain de performance)
     *
     * @param array $commands
     * @return array
     * @throws RedisException
     */
    public function pipe(array $commands): array
    {
        $s = [];
        $c = '';        
        $i = 0;

        foreach ($commands as $command) {
            $args = str_getcsv($command, ' ', '"');
			$c   .= $this->command($args);
            $i++;
        }

        $this->write($c);
        
        for ($j = 0; $j < $i; $j++) {
            $s[$j] = $this->read();
        }
        
        return $s;
    }

    /**
     * Retourne les informations sur le serveur Redis
     *
     * @param string $key information precise que l'on souhaite recuperer
     * @return array|bool|mixed
     * @throws RedisException
     */
    public function info(string $key = null)
    {
        $info   = [];
        $items  = explode("\r\n", $this->__call('info',[]));

        foreach ($items as $item) {
            $item = explode(":", $item);
            
            if (isset($item[1])) {
                $info[$item[0]] = $item[1];
            }
        }
        
        if ($key) {
            return isset($info[$key]) ? $info[$key] : false;
        }

        return $info;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws RedisException
     */
    public function exists(string $key)
    {
        return $this->__call("EXISTS", [$key]);
    }

    /**
     * @param string $key
     * @param $value
     * @return mixed
     * @throws RedisException
     */
    public function set(string $key, $value)
    {
        return $this->__call("SET", [$key, $value]);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws RedisException
     */
    public function get(string $key)
    {
        return $this->__call("GET", [$key]);
    }

    /**
     * @param string $key
     * @return bool
     * @throws RedisException
     */
    public function del(string $key)
    {
        return (bool) $this->__call("DEL", [$key]);
    }

    /**
     * Appel des commandes redis au travers de la surcharge magique de PHP
     *
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws RedisException
     */
    public function __call(string $name, array $args)
    {
        array_unshift($args, str_replace('_', ' ', strtoupper($name)));
			
        $this->write($this->command($args));
        return $this->read();
    }

    /**
     * @param string $key
     * @return mixed
     * @throws RedisException
     */
    public function __isset(string $key)
    {
        return $this->__call("EXISTS", [$key]);
    }

    /**
     * @param string $key
     * @param $value
     * @return mixed
     * @throws RedisException
     */
    public function __set(string $key, $value)
    {
        return $this->__call("SET", array($key, $value));
    }

    /**
     * @param string $key
     * @return mixed
     * @throws RedisException
     */
    public function __get(string $key)
    {
        return $this->__call("GET", array($key));
    }

    /**
     * @param string $key
     * @return mixed
     * @throws RedisException
     */
    public function __unset(string $key)
    {
        return $this->__call("DEL", array($key));
    }
}