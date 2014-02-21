###FANTASY FOOTBALL###

@todo

2. setup salary pemain di master.

3. buat API server.

a. create user -> done
b. select team for user (can only be use once) -> done
c. select players for user default to the user's choosen team. (can only be use once) -> done
d. set lineup. -> DONE
e. view fixtures -> DONE
f. view player stats (master stats, and player specific stats) -> DONE
g. view balance / budget -> DONE
h. view match reports -> DONE
i. view transfer window
j. check if transfer window is opened.
k. check if user can still set a lineups.
l. buy a player
m. sell a player 
n. view available sponsorships -> DONE
o. sign a sponsorship -> DONE
p. view available staff to recruit -> DONE
q. hire a staff -> DONE
r. sack a staff -> DONE
s. dummy for sponsorship page -> DONE

s. view financial statements -> DONE

4. fix issue where the JSON Output unable to convert utf8 character correctly.

5. harus ada table reference untuk mendata tanggal untuk tiap2 matchday.

6. masukan perhitungan gaji weekly pemain didalam financial statements.

example : 

matchday | matchdate
----------------------------
1		 | 22/08/2013
2        | 23/08/2013


6. make sure the newly created team, must given an initial budget of  GBP xxxxx -> DONE


s. view financial statements -> DONE


#ini menyusul
i. view transfer window
l. buy a player
m. sell a player 
o. harus buat mekanisme dimana pemain yg kena kartu merah atau cedera, gak bisa di pasang di formasi
p. bug di initial budget pas baru create.. malah jadi 200jt.. padahal harusnya 100jt.



#ALTER TABLE `ffgame`.`master_player`     ADD COLUMN `salary` INT(11) DEFAULT '200000' NULL AFTER `team_id`
;
ALTER TABLE `ffgame`.`master_player`     ADD COLUMN `transfer_value` INT(11) DEFAULT '10000000' NULL AFTER `salary`;
p. win bonus di frontend kemungkinan belum tercatat.

q. Bot for updating game_fixtures from OPTA file.



<option value="2">BALI</option>
	<option value="3">BANGKA-BELITUNG</option>
	<option value="4">BANTEN</option>
	<option value="5">BENGKULU</option>
	<option value="33">DI YOGYAKARTA</option>
	<option value="6">GORONTALO</option>
	<option value="7">IRIAN JAYA BARAT</option>
	<option value="8">JAKARTA RAYA</option>
	<option value="9">JAMBI</option>
	<option value="10">JAWA BARAT</option>
	<option value="11">JAWA TENGAH</option>
	<option value="12">JAWA TIMUR</option>
	<option value="13">KALIMANTAN BARAT</option>
	<option value="14">KALIMANTAN SELATAN</option>
	<option value="15">KALIMANTAN TENGAH</option>
	<option value="16">KALIMANTAN TIMUR</option>
	<option value="17">KEPULAUAN RIAU</option>
	<option value="18">LAMPUNG</option>
	<option value="19">MALUKU</option>
	<option value="20">MALUKU UTARA</option>
	<option value="1">NANGGROE ACEH DARUSSALAM</option>
	<option value="21">NUSA TENGGARA BARAT</option>
	<option value="22">NUSA TENGGARA TIMUR</option>
	<option value="23">PAPUA</option>
	<option value="24">RIAU</option>
	<option value="25">SULAWESI BARAT</option>
	<option value="26">SULAWESI SELATAN</option>
	<option value="27">SULAWESI TENGAH</option>
	<option value="28">SULAWESI TENGGARA</option>
	<option value="29">SULAWESI UTARA</option>
	<option value="30">SUMATERA BARAT</option>
	<option value="31">SUMATERA SELATAN</option>
	<option value="32">SUMATERA UTARA</option>



the cache that need to reset everytime the weekly stats is counted.
getPlayerTeamStats_
getPlayerDailyTeamStats_


TODO

cache the following in /api/gameplay.js
getPlayers
getPlayerStats
getPlayerOverallStats
best_player