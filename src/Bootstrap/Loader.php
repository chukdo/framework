<?php namespace Chukdo\Bootstrap;

/**
 * Gestion des exceptions
 *
 * @package 	Exception
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class LoaderException extends \Exception {}

/**
 * Class loader PSR-4
 *
 * @package 	bootstrap
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Loader
{
	/**
	 * Log 
	 *
	 * @var array
	 */
	private $log = [];
	
	/**
	 * Namespaces
	 *
	 * @var array
	 */
	private $namespaces = [];
	 
    /**
     * Constructeur
     * Initialise l'objet
     * 
     * @return	void
     */ 
    public function __construct() {}
		
	/**
     * Register loader with SPL autoloader stack.
     * 
     * @return	void
     */
	public function register(): void
	{
		spl_autoload_register(array($this, 'loadClass'));
	}
	
    /**
     * Unregisters this instance as an autoloader.
	 * 
     * @return	void
     */
	public function unregister(): void
	{
		spl_autoload_unregister(array($this, 'loadClass'));
	}
	
    /**
     * Registers a set of PSR-4 directories for a given namespace, either
     * appending or prepending to the ones previously set for this namespace.
     *
     * @param 	string       $ns The namespace
     * @param 	array|string $paths The base directories
     * @param 	bool         $prepend Whether to prepend the directories
     * @return	void
     */	
	public function registerNameSpace(string $ns, $paths, bool $prepend = false): void
	{
		/** normalize namespace */
        $ns = trim($ns, '\\');
		
		foreach ((array) $paths as $path) {
			
			/** normalize the base directory with a separator */
	        $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			
			/** initialize the namespace array */
	        if (isset($this->namespaces[$ns]) === false) {
	            $this->namespaces[$ns] = [];
	        }
	
	        /** retain the base directory for the namespace */
	        if ($prepend) {
	            array_unshift($this->namespaces[$ns], $path);
	        } else {
	            array_push($this->namespaces[$ns], $path);
	        }
		}
	}
	
    /**
     * Registers a set namespaces.
     *
     * @param 	array 	$namespaces array($namespace => $paths)
     * @return	void
     */	
	public function registerNameSpaces(array $namespaces): void
	{
		foreach ($namespaces as $ns => $paths) {
			$this->registerNameSpace($ns, $paths);
		}
	}
	
	/**
     * Loads the class file for a given class name.
     *
     * @param	string	$nsclass The fully-qualified class name.
     * @return	bool	true on success, or false on failure.
     */
	public function loadClass(string $nsclass): bool
	{
		$ns    		= explode('\\', $nsclass);
		$class 		= [];
		$class[]	= array_pop($ns);
		
		while(!empty($ns)) {
			if ($this->loadFile(implode('\\', $ns), implode('\\', $class))) {
				return true;
			}
			
			array_unshift($class, array_pop($ns));
		}
		
		return false;
	}

    /**
     * Load the file for a namespace and class.
     * 
     * @param 	string 	$ns The namespace.
     * @param 	string 	$class class name.
     * @return 	bool 	Boolean false if no file can be loaded, or true if the file that was loaded.
     */
	protected function loadFile(string $ns, string $class): bool
	{
		if (!isset($this->namespaces[$ns])) {
			return false;
		}
		
		foreach ($this->namespaces[$ns] as $path) {
			$file = $path.str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
			
			if ($this->requireFile($file)) {
                $this->log[$class] = $file;
				return true;
			}
		}
		
		return false;
	}

	/**
     * If a file exists, require it from the file system.
     * 
     * @param 	string 	$file The file to require.
     * @return 	bool 	True if the file exists, false if not.
     */
    protected function requireFile(string $file): bool
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }
}