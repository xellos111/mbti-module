<?php
/**
 * @class  mbtiAdminController
 * @author 
 * @brief  mbti module admin controller class
 */
class mbtiAdminController extends mbti
{
    /**
     * @brief Initialization
     */
    public function init()
    {
    }

    /**
     * @brief Save configuration
     */
    public function procMbtiAdminInsertConfig()
    {
        $vars = Context::getRequestVars();
        
        $config = new stdClass();
        $config->point_cost = isset($vars->point_cost) ? (int)$vars->point_cost : 500;
        $config->show_stats = isset($vars->show_stats) ? $vars->show_stats : 'Y';
        
        if (isset($vars->allowed_groups)) {
            $config->allowed_groups = is_array($vars->allowed_groups) ? $vars->allowed_groups : explode('|@|', $vars->allowed_groups);
        } else {
            $config->allowed_groups = [];
        }

        $oModuleController = getController('module');
        $output = $oModuleController->insertModuleConfig('mbti', $config);

        if(!$output->toBool()) {
            return $output;
        }

        $this->setMessage('success_saved');
        $returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispMbtiAdminIndex');
        $this->setRedirectUrl($returnUrl);
    }
}
