<?php namespace fan\core\service;
/**
 * Description of reflector
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.001 (10.03.2014)
 */
class reflector extends \fan\core\base\service\single
{
    /**
     * @var array List of Reflection classes
     */
    private $aReflection = array();
    /**
     * @var array Chains of parent classes
     */
    private $aParentChain = array();

    /**
     * Set new Reflection
     * @param string $sClassName
     * @return \fan\core\service\reflector
     */
    public function setReflection(&$sClassName)
    {
        if (is_object($sClassName)) {
            $sClassName = get_class($sClassName);
        }
        if (isset($this->aReflection[$sClassName])) {
            return $this;
        }

        $aParentChain = array();
        $oReflection  = new \ReflectionClass($sClassName);
        while (!empty($oReflection)) {
            $sTmpName = $oReflection->getName();
            if (isset($this->aReflection[$sTmpName])) {
                $aParentChain = array_merge($aParentChain, $this->aParentChain[$sTmpName]);
                break;
            }

            $aParentChain[$sTmpName]      = $oReflection;
            $this->aReflection[$sTmpName] = $oReflection;

            $oReflection = $oReflection->getParentClass();
        }

        foreach (array_keys($aParentChain) as $k) {
            if (isset($this->aParentChain[$k])) {
                break;
            }
            $this->aParentChain[$k] = $aParentChain;
            array_shift($aParentChain);
        }
        return $this;
    }

    /**
     * Get Reflection of Class
     * @return \ReflectionClass
     */
    public function getReflection($sClassName)
    {
        $this->setReflection($sClassName);
        return $this->aReflection[$sClassName];
    }

    /**
     * Get Parent Chain of Class
     * Start - current class; End - the oldest parent
     * @return array
     */
    public function getParentChain($sClassName)
    {
        $this->setReflection($sClassName);
        return $this->aParentChain[$sClassName];
    }

    /**
     * Get File Paths of Parent Chain
     * Start - current class; End - the oldest parent
     * @return array
     */
    public function getParentPaths($sClassName)
    {
        $aPaths = array();
        $aChain = $this->getParentChain($sClassName);
        foreach ($aChain as $k => $v) {
            $aPaths[$k] = $v->getFileName();
        }
        return $aPaths;
    }
} // class \fan\core\service\reflector
?>