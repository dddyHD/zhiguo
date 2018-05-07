<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Core\Controller;

use Think\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class ExpressionController extends Controller
{
    private $_emojis = "grinning,grimacing,grin,joy,smiley,smile,sweat_smile,laughing,innocent,wink,blush,slight_smile,upside_down,relaxed,yum,relieved,heart_eyes,kissing_heart,kissing,kissing_smiling_eyes,kissing_closed_eyes,stuck_out_tongue_winking_eye,stuck_out_tongue_closed_eyes,stuck_out_tongue,money_mouth,nerd,sunglasses,hugging,smirk,no_mouth,neutral_face,expressionless,unamused,rolling_eyes,thinking,flushed,disappointed,worried,angry,rage,pensive,confused,slight_frown,frowning2,persevere,confounded,tired_face,weary,triumph,open_mouth,scream,fearful,cold_sweat,hushed,frowning,anguished,cry,disappointed_relieved,sleepy,sweat,sob,dizzy_face,astonished,zipper_mouth,mask,thermometer_face,head_bandage,sleeping,zzz,poop,smiling_imp,imp,japanese_ogre,japanese_goblin,skull,ghost,alien,robot,smiley_cat,smile_cat,joy_cat,heart_eyes_cat,smirk_cat,kissing_cat,scream_cat,crying_cat_face,pouting_cat,raised_hands,clap,wave,thumbsup,thumbsdown,punch,fist,v,ok_hand,raised_hand,open_hands,muscle,pray,point_up,point_up_2,point_down,point_left,point_right,middle_finger,hand_splayed,metal,vulcan,writing_hand,nail_care,lips,tongue,ear,nose,eye,eyes,bust_in_silhouette,busts_in_silhouette,speaking_head,baby,boy,girl,man,woman,person_with_blond_hair,older_man,older_woman,man_with_gua_pi_mao,man_with_turban,cop,construction_worker,guardsman,spy,santa,angel,princess,bride_with_veil,walking,runner,dancer,dancers,couple,two_men_holding_hands,two_women_holding_hands,bow,information_desk_person,no_good,ok_woman,raising_hand,person_with_pouting_face,person_frowning,haircut,massage,couple_with_heart,couple_ww,couple_mm,couplekiss,kiss_ww,kiss_mm,family,family_mwg,family_mwgb,family_mwbb,family_mwgg,family_wwb,family_wwg,family_wwgb,family_wwbb,family_wwgg,family_mmb,family_mmg,family_mmgb,family_mmbb,family_mmgg,womans_clothes,shirt,jeans,necktie,dress,bikini,kimono,lipstick,kiss,footprints,high_heel,sandal,boot,mans_shoe,athletic_shoe,womans_hat,tophat,helmet_with_cross,mortar_board,crown,school_satchel,pouch,purse,handbag,briefcase,eyeglasses,dark_sunglasses,ring,closed_umbrella,cn,dog,cat,pig,heart,broken_heart";


    public function getEmoji()
    {
        $list = $this->_emojis;
        $list = $list = explode(',', $list);
        $res = array_chunk($list, 30);
        $this->ajaxReturn($res);
    }


}