/**
* we map our in-engine stats map with opta stats.
*/
exports.stats_map = {
	arial_duel: ['aerial_won'],
	assist: ['goal_assist','goal_assist_deadball'],
	attacking_3rd: ['final_third_entry','final_third_entries'],
	blocks_or_saves: ['diving_save','penalty_save','clearance_off_line',
					'dive_catch','att_freekick_target','att_ibox_blocked',
					'att_ibox_target','good_high_claim','saves','punches'],
	chance_created: ['accurate_corners_intobox','accurate_cross',
					'accurate_fwd_zone_pass','att_miss_high','att_miss_high_left',
					'att_miss_high_right','att_miss_left','att_miss_right'],
	forward_pass: ['fwd_pass'],
	goal: ['goals'],
	interceptions: ['interception_won'],
	long_pass: ['accurate_long_balls','accurate_back_zone_pass'],
	mom: ['man_of_the_match'],
	monthly_honour: ['monthly_honour'],
	own_goals: ['own_goals'],
	penalty: ['penalty_won'],
	recieve_pass: ['touches'],
	red_card: ['red_card'],
	shots_on_target: ['att_bx_centre','att_obx_centre','att_corner',
						'att_freekick_total','att_hd_target','shot_off_target'],
	square_pass: ['square_pass'],
	start_as_starter: ['starter'],
	start_as_sub: ['sub'],
	successfull_cross: ['freekick_cross','accurate_cross'],
	successfull_take_on: ['last_man_contest'],
	tackles: ['won_tackle'],
	yellow_card: ['yellow_card','second_yellow'],
};
exports.getStats = function(){
	var stats = {};
	for(var i in exports.stats_map){
		//stats[exports.stats_map[i]] = i;
		for(var j in exports.stats_map[i]){
			var item = exports.stats_map[i][j];
			stats[item] = i;
		}
	}
	return stats;
}