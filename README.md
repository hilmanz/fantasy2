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
o. harus buat mekanisme dimana pemain yg kena kartu merah atau cedera, gak bisa di pasang di formasi.



#ALTER TABLE `ffgame`.`master_player`     ADD COLUMN `salary` INT(11) DEFAULT '200000' NULL AFTER `team_id`
;

p. win bonus di frontend kemungkinan belum tercatat.

q. Bot for updating game_fixtures from OPTA file.