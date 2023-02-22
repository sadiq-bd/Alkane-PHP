<?php

namespace Core;

/**
 * Class ClassLoader
 *
 * @category  Class Handler
 * @package   ClassLoader
 * @author    Sadiq <sadiq.developer.bd@gmail.com>
 * @copyright Copyright (c) 2022-23
 * @version   2.0
 * @package   Alkane\ClassLoader
 */

 class ClassLoader {

     /**
      * @var array
      */
     private static $classPathMap = array();

     /**
      * @var bool $strip_namespace
      */
     public static $strip_namespace = false;

        /**
        * @var array
        */
     public static function add_paths($classPathMap) {
        self::$classPathMap = array_merge(self::$classPathMap, $classPathMap);
    }

     /**
      * @param $class
      */
     public static function init() {
         spl_autoload_register(array(__CLASS__, 'loadClass'));
     }

        /**
        * @param $class
        */
     public static function loadClass($class) {
        $path = array();
        foreach (self::$classPathMap as $val) {
            if (self::$strip_namespace == false) {
                $class = str_replace('\\', '/', $class);
                $class = trim($class, '/');
                $path[] = $val . '/' . $class . '.php';
            } else {
                $path[] = self::getFullClassPath($val, $class);
            }
        }

        foreach ($path as $val) {
            if (self::includeFile($val)) {
                return;
            }
        }
     }

        /**
        * @param $path
        * @param $class
        * @return string
        */
     public static function getFullClassPath($classPath, $className) {
         $className = self::stripNamespace($className);
         $classPath = rtrim($classPath, '/');
         $classPath .= '/' . $className . '.php';
         return $classPath;
     }

     /**
      * strip namespace from class name
      */
    public static function stripNamespace($className) {
        if (strpos($className, '\\')) {  // check if the class name contains namespace
            $class = explode('\\', $className);
            $class = count($class) > 1 ? $class[count($class) - 1] : $class[0];
            $className = $class;
        }
        return $className;
    }

    /**
     * @param bool $strip_namespace
     */
    public static function is_strip_namespace($strip_namespace) {
        self::$strip_namespace = $strip_namespace;
    }

    /**
     * @param $file
     * @return bool
     */
    public static function includeFile($file) {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }

     /**
      * unregister autoloader
      */
    public static function unregister() {
        spl_autoload_unregister(array(__CLASS__, 'loadClass'));
    }

 }

 