/**
*Stadium Income
High = Against Top 3 Teams in EPL
Standard = Against 4 - 10 Teams in EPL
Low = Against 11 - Bottom Teams in EPL

1st Quandrant (Top 25% Teams in Leaderboard) earns max 100%
High=(Attendance_Value*100%) * 100 USD
Standard=(Attendance_Value*100%) * 75 USD
Low=(Attendance_Value*100%) * 50 USD

2nd Quandrant (Second 25% Teams in Leaderboard) earns 75%
High=(Attendance_Value*75%) * 50 USD
Standard=(Attendance_Value*80%) * 30 USD
Low=(Attendance_Value*100%) * 25 USD

3rd Quandrant (Third 25% Teams in Leaderboard) earns 50%
High=(Attendance_Value*50%) * 100 USD
Standard=(Attendance_Value*75%) * 75 USD
Low=(Attendance_Value*85%) * 50 USD

4th Quandrant (Third 25% Teams in Leaderboard) earns 25%
High=(Attendance_Value*25%) * 100 USD
Standard=(Attendance_Value*50%) * 75 USD
Low=(Attendance_Value*75%) * 50 USD

* Commercial Director = earnings  + 15%
* Marketing Manager = Earnings + 10%
* Public Relations Officer = Earnings + 5%

*/
exports.stadium_earning_category = {
	high: {from:1,to:3},
	standard: {from:4,to:10},
	low: {from:11,to:20}
}
exports.cost_modifiers = {
	operating_cost: 0.4,
}
exports.stadium_earnings = {
	q1:{
		price:{
			high: 100,
			standard: 75,
			low: 50,
		},
		ratio: {
			high:1.0,
			standard:1.0,
			low:1.0,
		},
	},
	q2:{
		price:{
			high: 100,
			standard: 75,
			low: 50,
		},
		ratio: {
			high:0.75,
			standard:0.8,
			low:1.0,
		},
	},
	q3:{
		price:{
			high: 100,
			standard: 75,
			low: 50,
		},
		ratio: {
			high:0.5,
			standard:0.75,
			low:0.85,
		},
	},
	q4:{
		price:{
			high: 100,
			standard: 75,
			low: 50,
		},
		ratio: {
			high:0.25,
			standard:0.5,
			low:0.75,
		},
	},
}
/*
<option value="4-4-2">4-4-2</option>
			<option value="4-4-1-1">4-4-1-1</option>
			<option value="4-3-3">4-3-3</option>
			<option value="4-3-2-1">4-3-2-1</option>
			<option value="4-3-1-2">4-3-1-2</option>
			<option value="5-3-2">5-3-2</option>
			<option value="5-3-1-1">5-3-1-1</option>
			<option value="5-2-2-1">5-2-2-1</option>
			<option value="4-2-4">4-2-4</option>
			<option value="3-4-3">3-4-3</option>
			<option value="3-4-2-1">3-4-2-1</option>
*/
exports.formations = {
	'4-4-2': ['','Goalkeeper','Defender','Defender','Defender','Defender','Midfielder','Midfielder','Midfielder','Midfielder','Forward','Forward'],
	'4-4-1-1': ['','Goalkeeper','Defender','Defender','Defender','Defender','Midfielder','Midfielder','Midfielder','Midfielder','Forward','Forward'],
	'4-4-2-A': ['','Goalkeeper','Defender','Defender','Defender','Defender','Midfielder','Midfielder','Midfielder','Midfielder','Forward','Forward'],
	'4-3-3': ['','Goalkeeper','Defender','Defender','Defender','Defender','Midfielder','Midfielder','Midfielder','Forward','Forward','Forward'],
	'4-2-3-1' : ['','Goalkeeper','Defender','Defender','Defender','Defender','Midfielder','Forward/Midfielder','Midfielder','Forward/Midfielder','Forward','Forward/Midfielder'],
	'3-5-2' : ['','Goalkeeper','Defender','Defender','Midfielder','Defender','Midfielder','Midfielder','Midfielder','Midfielder','Forward','Forward'],
	'4-3-2-1': ['','Goalkeeper','Defender','Defender','Defender','Defender','Midfielder','Midfielder','Midfielder','Forward/Midfielder','Forward/Midfielder','Forward'],
	'4-3-1-2': ['','Goalkeeper','Defender','Defender','Defender','Defender','Midfielder','Midfielder','Midfielder','Forward/Midfielder','Forward','Forward'],
	'5-3-2': ['','Goalkeeper','Defender','Defender','Defender','Defender','Defender','Midfielder','Midfielder','Midfielder','Forward','Forward'],
	'5-3-1-1': ['','Goalkeeper','Defender','Defender','Defender','Defender','Defender','Midfielder','Midfielder','Midfielder','Forward/Midfielder','Forward'],
	'5-2-2-1': ['','Goalkeeper','Defender','Defender','Defender','Defender','Defender','Midfielder','Midfielder','Forward/Midfielder','Forward/Midfielder','Forward'],
	'4-2-4': ['','Goalkeeper','Defender','Defender','Defender','Defender','Midfielder','Midfielder','Forward/Midfielder','Forward/Midfielder','Forward','Forward'],
	'3-4-3': ['','Goalkeeper','Defender','Defender','Defender','Midfielder','Midfielder','Midfielder','Midfielder','Forward','Forward','Forward'],
	'3-4-2-1': ['','Goalkeeper','Defender','Defender','Defender','Midfielder','Midfielder','Midfielder','Midfielder','Forward/Midfielder','Forward/Midfielder','Forward'],
}

//initial amount of money the user will have.
exports.initial_money = 100000000;
exports.sponsorship_chance = 0.4;

exports.player_stats_category = {
	goals_and_assists:[
		'goals',
		'goal_assist',
		'big_chance_created',
		'penalty_won'
	],
	shooting:[
		'ontarget_scoring_att',
		'att_obox_target'
	],
	passing:[
		'accurate_flick_on',
		'accurate_pass',
		'accurate_chipped_pass',
		'accurate_launches',
		'accurate_layoffs',
		'accurate_long_balls',
		'accurate_through_balls',
		'long_pass_own_to_opp_success',
		'accurate_cross',
		'accurate_fwd_zone_pass',
		'accurate_freekick_cross'
	],
	defending:[
		'ball_recovery',
		'duel_won',
		'aerial_won',
		'tackle_won',
		'last_man_contest',
		'interception',
		'interceptions_in_box',
		'effective_clearence',
		'effective_blocked_cross',
		'effective_blocked_shot',
		'six_yard_block'
	],
	goalkeeping:[
		'penalty_save',
		'dive_save',
		'dive_catch',
		'stand_save',
		'stand_catch',
		'good_claim',
		'good_high_claim',
		'punches',
		'good_one_on_one',
		'gk_smother'
	],
	discipline:[
		'red_card',
		'yellow_card'
	],
	mistakes:[
		'penalty_conceded',
		'dispossessed',
		'error_lead_to_goal',
		'error_lead_to_shot',
		'poss_lost_ctrl',
		'unsuccessful_touch',
		'challenge_lost',
		'cross_not_claimed'
	]
};