<?php

namespace Orange\Lang;

/**
 * Class Lang (Singletone)
 */
class Lang
{

    /**
     * @var array
     */
    protected $loaded_texts = [];

    /**
     * @var null|string
     */
    protected $default_lang = null;

    /**
     * @var null|string
     */
    protected $current_lang = null;

    /**
     * @var array
     */
    protected static $lang_files_dirs = [];

    /**
     * @var Lang[]
     */
    protected static $instances = [];

    /**
     * Get language instance
     * @param null|string $current_lang
     * @param null|string $default_lang
     * @return Lang
     */
    public static function getInstance($current_lang = null, $default_lang = null){
        $prefix = (!is_null($current_lang) ? $current_lang : '##') . ':' . (!is_null($default_lang) ? $default_lang : '##');
        if (!isset(static::$instances[$prefix])){
            static::$instances[$prefix] = new Lang($current_lang, $default_lang);
        }
        return static::$instances[$prefix];
    }

    /**
     * Protected constructor
     * @param null|string $current_lang
     * @param null|string $default_lang
     */
    protected function __construct($current_lang, $default_lang){
        $this->setCurrentLanguage($current_lang);
        $this->setDefaultLanguage($default_lang);
    }

    /**
     * Reset all loaded texts. Should be called on setCurrentLanguage and setDefaultLanguage callings.
     * @return $this
     */
    public function resetLoadedTexts()
    {
        $this->loaded_texts = [];
        return $this;
    }

    /**
     * Set default language. If it will be null, script use roots of language directories.
     * @param string $default_lang
     * @return $this
     */
    public function setDefaultLanguage($default_lang)
    {
        $this->default_lang = $default_lang;
        return $this;
    }

    /**
     * Set current language
     * @param string $current_lang
     * @return $this
     */
    public function setCurrentLanguage($current_lang)
    {
        $this->current_lang = $current_lang;
        return $this;
    }

    /**
     * Add language files directory
     * @param string $dir
     * @return $this
     */
    public static function addLangFilesDir($dir)
    {
        $dir = rtrim(realpath($dir), '/');
        static::$lang_files_dirs[$dir] = $dir;
    }

    /**
     * Exclude language files directory
     * @param string $dir
     * @return $this
     */
    public static function excludeLangFilesDir($dir)
    {
        $dir = rtrim(realpath($dir), '/');
        unset(static::$lang_files_dirs[$dir]);
    }

    /**
     * Get text (translation) from first defined Lang object
     * @param string $selector
     * @param null|string $default
     * @return string
     * @throws \Exception
     */
    public static function t($selector, $default = null){
        if (!static::$instances){
            throw new \Exception('Language object is not created yet.');
        }
        return static::$instances[key(static::$instances)]->get($selector, $default);
    }

    /**
     * Get text (translation)
     * @param string $selector
     * @param null|string $default
     * @return string
     * @throws \Exception
     */
    public function get($selector, $default = null)
    {
        if (empty(self::$lang_files_dirs)) {
            throw new \Exception('There is no language directories defined.');
        }
        list($file, $selector) = explode('.', $selector, 2);
        if (empty($selector)) {
            throw new \Exception('Incorrect selector ' . $selector);
        }
        if (!array_key_exists($file, $this->loaded_texts)) {
            static::loadFile($file);
        }
        return array_key_exists($selector, $this->loaded_texts[$file])
            ? $this->loaded_texts[$file][$selector]
            : (!is_null($default) ? $default : $file . '.' . $selector);
    }

    /**
     * @param string $lang_file_name
     * @throws \Exception
     */
    protected function loadFile($lang_file_name)
    {
        $file = 'Unknown file';
        foreach (static::$lang_files_dirs as $dir) {
            if (is_file($file = $dir . (!is_null($this->default_lang) ? '/' . $this->default_lang : '') . '/' . $lang_file_name . '.php')) {
                $lng = include $file;
                if (!is_array($lng)) {
                    throw new \Exception('Incorrect lang file ' . $file . 'is not found in defined language directories.');
                }
                $this->loaded_texts[$lang_file_name] = $lng;
                break;
            }
        }
        if (!is_null($this->current_lang)) {
            foreach (static::$lang_files_dirs as $dir) {
                if (is_file($file = $dir . '/' . $this->current_lang . '/' . $lang_file_name . '.php')) {
                    $lng = include $file;
                    if (!is_array($lng)) {
                        throw new \Exception('Incorrect lang file ' . $file . 'is not found in avaliable lang directories.');
                    }
                    $this->loaded_texts[$lang_file_name] = array_key_exists($lang_file_name, $this->loaded_texts)
                        ? array_merge($this->loaded_texts[$lang_file_name], $lng)
                        : $lng;
                    break;
                }
            }
        }
        if (!array_key_exists($lang_file_name, $this->loaded_texts)) {
            throw new \Exception('Language file ' . $file . ' is not found in avaliable lang directories.');
        }
    }

}