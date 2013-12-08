<?php
/**
* Game Fixtures Monitoring.
*
*/
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');

class MerchandisesController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Merchandises';
	public $components = array('Thumbnail');
	public function index(){
		
	}
	public function categories(){
		$this->loadModel('MerchandiseCategory');
		$this->loadModel('MerchandiseItem');
		$category = $this->MerchandiseCategory->find('all',array('limit'=>100));
		$this->set('rs',$category);

	}

	public function get_items(){
		$this->layout = 'ajax';
		$this->loadModel('MerchandiseItem');
		$start = intval(@$this->request->query['start']);
		$limit = 20;
		$this->MerchandiseItem->bindModel(
			array('belongsTo'=>array('MerchandiseCategory'))
		);
		$rs = $this->MerchandiseItem->find('all',array('offset'=>$start,'limit'=>$limit));
		
		$this->set('response',array('status'=>1,'data'=>$rs,'next_offset'=>$start+$limit,'rows_per_page'=>$limit));
		$this->render('response');
		
	}
	public function edit($id){
		$this->loadModel('MerchandiseItem');
		

		if($this->request->is('post')){
			$this->MerchandiseItem->id = $id;
			$this->MerchandiseItem->save($this->request->data);
			if(isset($_FILES['pic']['name'])){
				$this->update_pic($id);
			}
			$this->Session->setFlash('Update Completed !');
		}
		$rs = $this->MerchandiseItem->findById($id);
		$this->set('rs',$rs);
		$this->loadModel('MerchandiseCategory');
		$categories = $this->MerchandiseCategory->find('all',array('limit'=>100));
		$this->set('categories',$categories);


	}
	private function update_pic($id){
		$dir_path = Configure::read('avatar_img_dir')."merchandise/";
		$filename = $_FILES['pic']['name'];
		$dir = new Folder($dir_path, true, 0777);
		$dir->chmod($dir_path,0777,false);
		if(move_uploaded_file($_FILES['pic']['tmp_name'],
				$dir_path.$filename)){
				//is it an image by guessing its extensions ?
				preg_match('/([^\s]+(\.(?i)(jpg|png|gif))$)/',$filename,$matches);
				if(sizeof($matches)>0){
					$this->createThumbnail($dir_path,
											$filename);
				}
			$this->MerchandiseItem->id = $id;
			$this->MerchandiseItem->save(array('pic'=>$filename));	
		}
		$dir->chmod($dir_path,0755,false);
	}
	public function create(){
		$this->loadModel('MerchandiseItem');
		if($this->request->is('post')){
			$dir_path = Configure::read('avatar_img_dir')."merchandise/";
			$filename = $_FILES['pic']['name'];
			$dir = new Folder($dir_path, true, 0777);
			$dir->chmod($dir_path,0777,false);

			if(move_uploaded_file($_FILES['pic']['tmp_name'],
					$dir_path.$filename)){
					//is it an image by guessing its extensions ?
					preg_match('/([^\s]+(\.(?i)(jpg|png|gif))$)/',$filename,$matches);
					if(sizeof($matches)>0){
						$this->createThumbnail($dir_path,
												$filename);
					}
					
			}
			$dir->chmod($dir_path,0755,false);

			$this->request->data['pic'] = $filename;
			$this->MerchandiseItem->create();
			$rs = $this->MerchandiseItem->save($this->request->data);
			if($rs){
				$this->Session->setFlash('New Merchandise has been added successfully !');
			}else{
				$this->Session->setFlash('Cannot add the merchandise, please try again later!');
			}
		}
		$this->loadModel('MerchandiseCategory');
		$categories = $this->MerchandiseCategory->find('all',array('limit'=>100));
		$this->set('categories',$categories);

	}
	public function add_category(){
		if($this->request->is('post')){
			$this->loadModel('MerchandiseCategory');	
			$this->MerchandiseCategory->create();
			$rs = $this->MerchandiseCategory->save($this->request->data);
			if($rs){
				$this->Session->setFlash('New Category has been saved successfully !');
			}else{
				$this->Session->setFlash('Cannot create the category, please try again later!');
			}
		}
		$this->redirect('/merchandises/categories');
	}
	public function delete_category($id){
		$id = intval(Sanitize::clean($id));
		$this->loadModel('MerchandiseCategory');	
		
		$rs = $this->MerchandiseCategory->delete($id);
		if($rs){
			$this->Session->setFlash('The Category has been deleted successfully !');
		}else{
			$this->Session->setFlash('Cannot delete the category, please try again later!');
		}
		$this->redirect('/merchandises/categories');
	}
	private function createThumbnail($upload_dir,$filename){
		$tsize = Configure::read('THUMBNAIL_SIZES');
		$thumb_dir = $upload_dir.'/thumbs';
		$dir = new Folder($thumb_dir, true, 0777);
		$dir->chmod($thumb_dir,0777,false);

		$c = $this->Thumbnail->resizeImage('resize', $filename, 
							$upload_dir, 
							"/thumbs/0_".$filename, 
							400, 
							400, 
							100);

		$c = $this->Thumbnail->resizeImage('resize', $filename, 
							$upload_dir, 
							"/thumbs/1_".$filename, 
							200, 
							200, 
							100);

		$c = $this->Thumbnail->resizeImage('resize', $filename, 
							$upload_dir, 
							"/thumbs/2_".$filename, 
							75, 
							75, 
							100);

		$dir->chmod($thumb_dir,0755,false);
	}
	public function orders(){
		//i dunno what to do yet.
	}
	
	public function get_orders(){
		$this->layout = 'ajax';
		$this->loadModel('MerchandiseOrder');
		$start = intval(@$this->request->query['start']);
		$limit = 20;
		$this->MerchandiseOrder->bindModel(
			array('belongsTo'=>array('MerchandiseItem'))
		);
		$rs = $this->MerchandiseOrder->find('all',array('offset'=>$start,'limit'=>$limit));
		
		$this->set('response',array('status'=>1,'data'=>$rs,'next_offset'=>$start+$limit,'rows_per_page'=>$limit));
		$this->render('response');
	}
	private function refund(){
		//make sure that the transaction is not yet canceled


		//make sure that the deducted fund is exists
		//$this->validatePurchaseFund($game_team_id,$po_number);


		//if everything is fine, then we process the refund

		//then set n_status of order to 4 (canceled)
	}
	private function validatePurchaseFund($game_team_id,$po_number){
		$sql = "SELECT id FROM ffgame.game_team_expenditures a
				WHERE game_team_id={$game_team_id} AND 
				item_name ='purchase merchandise - {$po_number}' LIMIT 1;";
		$rs = $this->Game->query($sql);
		if(intval($rs[0]['a']['id']) > 0){
			return true;
		}
	}
}