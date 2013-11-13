<?php

function stats_translated($stats_name,$locale='id'){
	/**
	* these is a temporary solution until we use the cakephp's localization feature
	*/
	$STATS_NAME['id'] = array(
		 //games
		 'game_started'=>'Dipasang di pertandingan BPL',
		 'sub_on'=>'Main sebagai cadangan',
		 //passing and attacking
	  	'Freekick Goal'=>'Mencetak gol dari tendangan bebas',
	    'Goal inside the box'=>'Mencetak gol dari dalam kotak pinalti',
	    'Goal Outside the Box'=>'Mencetak gol dari luar kotak pinalti',
	    'Penalty Goal'=>'Mencetak gol dari tendangan pinalti',
	    'Freekick Shots'=>'Mengambil tendangan bebas yang mengenai tiang gawang',
	    'On Target Scoring Attempt'=>'Melakukan tendangan dengan akurat',
	    'Shot From Outside the Box'=>'Melakukan tendangan dengan akurat dari luar kotak pinalti',
	    'big_chance_created'=>'Berperan menciptakan sebuah peluang matang',
	    'big_chance_scored'=>'Berperan menciptakan sebuah peluang matang yang akhirnya menjadi gol',
	    'goal_assist'=>'Melakukan assist',
	    'total_assist_attempt'=>'Melahirkan peluang yang berakhir dengan tendangan ke gawang',
	    'Second Goal Assist'=>'Mengawali serangan yang berakhir dengan terjadinya sebuah gol',
	    'final_third_entries'=>'Mengumpan atau melakukan dribbling ke daerah pertahanan lawan',
	    'fouled_final_third'=>'Dilanggar di daerah pertahanan lawan',
	    'pen_area_entries'=>'Mengumpan atau melakukan dribbling ke dalam kotak pinalti lawan',
	    'won_contest'=>'Berhasil menggocek lawan',
	    'won_corners'=>'Mendapatkan tendangan pojok',
	    'penalty_won'=>'Mendapatkan tendangan pinalti',
	    'last_man_contest'=>'Menggocek pemain pertahanan terakhir lawan',
	    'accurate_corners_intobox'=>'Melakukan tendangan pojok yang akurat ke dalam kotak pinalti',
	    'accurate_cross_nocorner'=>'Melakukan crossing dengan akurat',
	    'accurate_freekick_cross'=>'Melakukan crossing dengan akurat dari sebuah tendangan bebas',
	    'accurate_launches'=>'Menghalau bola dari daerah pertahanan, dan diterima oleh kawan satu tim',
	    'long_pass_own_to_opp_success'=>'Meluncurkan serangan dari dalam daerah pertahanan',
	    'successful_final_third_passes'=>'Melakukan umpan akurat di dalam daerah pertahanan lawan',
	    'accurate_flick_on'=>'Meneruskan umpan udara dengan menggunakan sundulan',
	    //defending
	    'aerial_won'=>'Memenangi duel di udara',
        'ball_recovery'=>'Menguasai bola liar / lepas',
        'duel_won'=>'Memenangi duel perebutan bola',
        'effective_blocked_cross'=>'Berhasil menggagalkan crossing lawan',
        'effective_clearance'=>'Berhasil menghalau serangan lawan',
        'effective_head_clearance'=>'Berhasil menghalau serangan lawan dengan menggunakan sundulan',
        'interceptions_in_box'=>'Memotong umpan lawan di dalam kotak pinalti',
        'interception_won' => 'Memotong umpan lawan',
        'possession_won_def_3rd' => 'Merebut bola di daerah pertahanan',
        'possession_won_mid_3rd' => 'Merebut bola di daerah tengah lapangan',
        'possession_won_att_3rd' => 'Merebut bola di daerah menyerang',
        'won_tackle' => 'Melakukan tackling dan merebut bola',
        'offside_provoked' => 'Berhasil melakukan perangkap offside ',
        'last_man_tackle' => 'Melakukan tackling dan merebut bola saat berposisi sebagai pemain belakang terakhir',
        'outfielder_block' => 'Menahan /menutup tendangan ke arah gawang',

        //goalkeeping
        'dive_catch'=> 'Melompat dan menangkap tendangan lawan',
        'dive_save'=> 'Melompat dan menepis tendangan lawan',
        'stand_catch'=> 'Menangkap tendangan lawan tanpa melompat',
        'stand_save'=> 'Menepis tendangan lawan tanpa melompat',
        'cross_not_claimed'=> 'Gagal menangkap crossing lawan',
        'good_high_claim'=> 'Berhasil menangkap crossing lawan',
        'punches'=> 'Memukul dan menghalau crossing lawan',
        'good_one_on_one'=> 'Menggagalkan serangan lawan dalam posisi 1v1',
        'accurate_keeper_sweeper'=> 'Berhasil keluar dari kotak pinalti untuk menghalau bola',
        'gk_smother'=> 'Mengamankan umpan terobosan lawan',
        'saves'=> 'Menyelamatkan tendangan lawan',
        'goals_conceded'=>'Kebobolan',

        //mistakes and errors
	    'penalty_conceded'=>'Menyebabkan terjadinya pinalti',
        'red_card'=>'Mendapat kartu merah',
        'yellow_card'=>'Mendapat kartu kuning',
        'challenge_lost'=>'Gagal melakukan tackling sehingga dilewati lawan',
        'dispossessed'=>'Kehilangan bola',
        'fouls'=>'Melakukan pelanggaran',
        'overrun'=>'Terlalu jauh mendorong bola saat berusaha dribbling',
        'total_offside'=>'Tertangkap offside',
        'unsuccessful_touch'=>'Gagal mengontrol bola',
        'error_lead_to_shot'=>'Melakukan kesalahan yang mengakibatkan peluang buat lawan',
        'error_lead_to_goal'=>'Melakukan kesalahan yang mengakibatkan gol lawan'

	);
	
	return $STATS_NAME[$locale][$stats_name];
}
?>