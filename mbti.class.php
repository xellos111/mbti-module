<?php
/**
 * @class  mbti
 * @author 
 * @brief  mbti module high class
 */
class mbti extends ModuleObject
{
    /**
     * @brief Install the module
     */
    public function moduleInstall()
    {
        return new BaseObject();
    }

    /**
     * @brief Check if update is needed
     */
    public function checkUpdate()
    {
        return false;
    }

    /**
     * @brief Update the module
     */
    public function moduleUpdate()
    {
        return new BaseObject();
    }

    /**
     * @brief Recompile cache
     */
    public function recompileCache()
    {
    }
}
