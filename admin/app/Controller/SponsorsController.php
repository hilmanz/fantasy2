<?php
/**
* Game Fixtures Monitoring.
*
*/
App::uses('AppController', 'Controller');


class SponsorsController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Sponsors';
	
	public function index(){
		$this->loadModel('Sponsorship');
		$this->paginate = array('limit'=>25);
		$sponsors = $this->paginate('Sponsorship');
		foreach($sponsors as $n=>$v){
			$sponsors[$n]['perks'] = $this->Sponsorship->getPerksBySponsorId($v['Sponsors']['id']);
		}

		$this->set('sponsors',$sponsors);
		
		//attach perks on json
		$perks = $this->Sponsorship->getPerks();
		$this->set('perks',$perks);
	}
	public function add_perk(){
		$this->layout="ajax";
		if($this->request->is('post')){
			$this->loadModel('Sponsorship');
			$rs = $this->Sponsorship->addPerk($this->request->data);
			if($rs){
				$this->set('response',array('status'=>1));	
			}else{
				$this->set('response',array('status'=>0));
			}
		}else{
			$this->set('response',array('status'=>0));
		}
		$this->render('response');
	}

	public function create(){
		$this->loadModel('Sponsorship');
		if($this->request->is('post')){
			$this->Sponsorship->create();
			$rs = $this->Sponsorship->save($this->request->data);
			if($rs){
				$this->Session->setFlash("new sponsorship has been saved successfully !");
				$this->redirect('/sponsors/edit/'.$this->Sponsorship->id);
			}else{
				$this->Session->setFlash("Cannot save the sponsorship, please try again later !");
			}
		}
	}
	public function create_perk(){
		$this->loadModel('Perk');
		if($this->request->is('post')){
			$this->Perk->create();
			$rs = $this->Perk->save($this->request->data);
			if($rs){
				$this->Session->setFlash("new perk has been saved successfully !");
				$this->redirect('/sponsors/perks');
			}else{
				$this->Session->setFlash("Cannot save the perk, please try again later !");
			}
		}
	}
	public function edit_perk($id){
		$this->loadModel('Perk');
		
		
		//posting data (if any)
		if($this->request->is('post')){
			$this->Perk->id = $id;
			$rs = $this->Perk->save($this->request->data);
			if($rs){
				$this->Session->setFlash("The changes has been saved successfully !");
			}else{
				$this->Session->setFlash("Cannot save the changes, please try again later !");
			}
		}//-->

		//load sponsorship details
		$rs = $this->Perk->findById($id);
		
		$this->set('perk',$rs['Perk']);
	}
	public function edit($id){
		$this->loadModel('Sponsorship');
		$this->loadModel('SponsorBanner');

		//attach perks on json
		$perks = $this->Sponsorship->getPerks();
		$this->set('perks',$perks);

		//load banners
		$banners = $this->SponsorBanner->find('all',array('conditions'=>array('sponsor_id'=>$id),
														'limit'=>200));
		$this->set('banners',$banners);

		//posting data (if any)
		if($this->request->is('post')){
			$this->Sponsorship->id = $id;
			$rs = $this->Sponsorship->save($this->request->data);
			if($rs){
				$this->Session->setFlash("The changes has been saved successfully !");
			}else{
				$this->Session->setFlash("Cannot save the changes, please try again later !");
			}
		}//-->

		//load sponsorship details
		$rs = $this->Sponsorship->findById($id);
		$rs['Sponsorship']['perks'] = $this->Sponsorship->getPerksBySponsorId($rs['Sponsorship']['id']);
		$this->set('sponsor',$rs['Sponsorship']);
	}
	//upload banner assets
	public function upload($id){
		$_FILES['file']['name'] = str_replace(array(' ','\''),"_",$_FILES['file']['name']);
		$this->set('sponsor_id',$id);
		if(move_uploaded_file($_FILES['file']['tmp_name'],
				Configure::read('avatar_img_dir').$_FILES['file']['name'])){
			
			//save to db
			$data = array(
				'banner_name'=>$this->request->data['name'],
				'url'=>$this->request->data['url'],
				'banner_file'=>$_FILES['file']['name'],
				'slot'=>$this->request->data['slot'],
				'sponsor_id'=>$id,
				'upload_date'=>date("Y-m-d H:i:s")
			);
			
			$this->loadModel('SponsorBanner');
			$rs = $this->SponsorBanner->save($data);
			$this->set('success',1);
		}else{
			$this->set('success',0);
		}
	}

	public function perks(){
		$this->loadModel('Perk');
		$rs = $this->Perk->find('all');
		$this->set('rs',$rs);
	}

	public function invite($id){
		$this->loadModel('Sponsorship');
		$this->loadModel('SponsorBanner');
		//load banners
		$banners = $this->SponsorBanner->find('all',array('conditions'=>array('sponsor_id'=>$id),
														'limit'=>200));
		$this->set('banners',$banners);

		//load sponsorship details
		$rs = $this->Sponsorship->findById($id);
		$rs['Sponsorship']['perks'] = $this->Sponsorship->getPerksBySponsorId($rs['Sponsorship']['id']);
		$this->set('sponsor',$rs['Sponsorship']);
	}
	public function queueing(){
		$this->loadModel('Sponsorship');
		$this->layout = 'ajax';
		$filter = $this->request->query['filter'];
		$email_type = $this->request->query['email_type'];
		$sponsor_id = intval($this->request->query['sponsor_id']);
		$start = intval($this->request->query['start']);

		$sponsor = $this->Sponsorship->findById($sponsor_id);
		$rs = $this->Sponsorship->query("SELECT a.fb_id,c.email,b.id AS game_team_id
										FROM ffgame.game_users a
										INNER JOIN ffgame.game_teams b
										ON a.id = b.user_id
										INNER JOIN ffg.users c
										ON a.fb_id = c.fb_id LIMIT {$start},20;
										");

		$total_scan = sizeof($rs);
		if($filter=='everyone_once'){
			$in_queue = $this->queue_everyone_once($sponsor_id,$rs,$email_type,$sponsor['Sponsorship']);
		}else{
			$in_queue = $total_scan;
		}
		
		if($start!=10000){
			$this->set('response',array('status'=>1,'total'=>$total_scan,'in_queue'=>$in_queue));	
		}else{
			$this->set('response',array('status'=>1,'total'=>0));	
		}
		
		$this->render('response');
	}
	private function queue_everyone_once($sponsor_id,$rs,$email_type,$sponsor){
		$in_queue = 0;

		foreach($rs as $n=>$v){
			//check for queue
			$cek = $this->Sponsorship->query("SELECT * FROM ffgame.game_sponsor_emails 
												WHERE sponsor_id={$sponsor_id} 
												AND game_team_id={$v['b']['game_team_id']}
												AND email_type='{$email_type}' LIMIT 1");
			if(sizeof($cek)==0){
				$in_queue++;
				$apply_link = $this->generate_apply_link($sponsor_id,
														 $v['b']['game_team_id'],
														 $v['c']['email'],
														 $email_type);
				//put into queue
				$q = $this->Sponsorship->query("INSERT INTO ffgame.game_sponsor_emails
											(sponsor_id,game_team_id,email_type,email,apply_link,sent_dt)
											VALUES
											({$sponsor_id},{$v['b']['game_team_id']},'{$email_type}',
											 '{$v['c']['email']}','{$apply_link}',NOW())");

				if(is_array($q)){
					//@TODO - we have to also create HTML version of email body :(
					//queue email
					//invitation_email
					if($email_type=='invitation'){
						$plain = str_replace("{{APPLY_LINK}}",Configure::read('WWW_URL').$apply_link,$sponsor['invitation_email']);
					}else{
						$plain = str_replace("{{APPLY_LINK}}",Configure::read('WWW_URL').$apply_link,$sponsor['win_bonus_email']);
					}
					$plain = mysql_escape_string($plain);
					$this->Sponsorship->query("INSERT INTO ffgame.email_queue
												(email,plain_txt,html_text,queue_dt,send_dt,n_status)
												VALUES
												('{$v['c']['email']}','{$plain}','{$plain}',NOW(),NULL,0);");
				}
			}
		}
		return $in_queue;
	}

	private function generate_apply_link($sponsor_id,$game_team_id,$email,$email_type){
		$data = serialize(array('sponsor_id'=>$sponsor_id,
					  'game_team_id'=>$game_team_id,
					  'email'=>$email,
					  'email_type'=>$email_type));

		$hash = encrypt_param($data);
		return "/sponsors/apply/?c=".$hash;
	}
}
