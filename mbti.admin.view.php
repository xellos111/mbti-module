<?php
/**
 * @class  mbtiAdminView
 * @author 
 * @brief  mbti module admin view class
 */
class mbtiAdminView extends mbti
{
    /**
     * @brief Initialization
     */
    public function init()
    {
        // Set template path
        $this->setTemplatePath($this->module_path . 'tpl');
    }

    /**
     * @brief Admin index (Configuration)
     */
    public function dispMbtiAdminIndex()
    {
        $oMbtiModel = getModel('mbti');
        $config = $oMbtiModel->getConfig();
        if(!isset($config->allowed_groups) || !is_array($config->allowed_groups)) $config->allowed_groups = [];
        Context::set('config', $config);

        $oMemberModel = getModel('member');
        $group_list = $oMemberModel->getGroups(0);
        Context::set('group_list', $group_list);

        $stats = $oMbtiModel->getCommunityStats();
        Context::set('mbti_stats', $stats);

        $total_count = 0;
        if(is_array($stats)) {
            foreach($stats as $stat) {
                $total_count += $stat->count;
            }
        }
        Context::set('mbti_total_count', $total_count);

        $this->setTemplateFile('index');
    }
}
