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
}
