<?php
/**
 * @class  mbtiController
 * @author 
 * @brief  mbti module controller class
 */
class mbtiController extends mbti
{
    /**
     * @brief Initialization
     */
    public function init()
    {
    }

    /**
     * @brief Submit the MBTI test, calculate result, deduct capsule, and save to DB
     */
    public function procMbtiSubmit()
    {
        $logged_info = Context::get('logged_info');
        if(!$logged_info) return new BaseObject(-1, 'msg_not_permitted');

        $member_srl = $logged_info->member_srl;
        $answers = Context::get('answers');

        if(!$answers || !is_array($answers) || count($answers) < 40) {
            return new BaseObject(-1, '모든 문항에 답변해주세요.');
        }

        // Calculate scores
        $scores = [
            'E' => 0, 'I' => 0,
            'S' => 0, 'N' => 0,
            'T' => 0, 'F' => 0,
            'J' => 0, 'P' => 0
        ];

        foreach($answers as $val) {
            if(isset($scores[$val])) {
                $scores[$val]++;
            }
        }

        // Determine Type
        $type = '';
        $type .= ($scores['E'] >= $scores['I']) ? 'E' : 'I';
        $type .= ($scores['S'] >= $scores['N']) ? 'S' : 'N';
        $type .= ($scores['T'] >= $scores['F']) ? 'T' : 'F';
        $type .= ($scores['J'] >= $scores['P']) ? 'J' : 'P';

        // Deduct Point (Capsule)
        $oMbtiModel = getModel('mbti');
        $config = $oMbtiModel->getConfig();
        $cost = (int)$config->point_cost;

        if($cost > 0) {
            $oPointModel = getModel('point');
            $current_point = $oPointModel->getPoint($member_srl);
            if($current_point < $cost) {
                return new BaseObject(-1, sprintf('캡슐(포인트)이 부족합니다. (현재: %s, 필요: %s)', $current_point, $cost));
            }

            $oPointController = getController('point');
            $oPointController->setPoint($member_srl, -$cost, 'minus');
        }

        // Insert into DB
        $args = new stdClass();
        $args->result_srl = getNextSequence();
        $args->member_srl = $member_srl;
        $args->mbti_type = $type;
        $args->score_e = $scores['E'];
        $args->score_i = $scores['I'];
        $args->score_s = $scores['S'];
        $args->score_n = $scores['N'];
        $args->score_t = $scores['T'];
        $args->score_f = $scores['F'];
        $args->score_j = $scores['J'];
        $args->score_p = $scores['P'];

        $res = executeQuery('mbti.insertResult', $args);
        if(!$res->toBool()) return $res;

        $this->add('result_srl', $args->result_srl);
        $this->add('mbti_type', $type);
        $this->setMessage('검사가 완료되었습니다.');
    }
}
