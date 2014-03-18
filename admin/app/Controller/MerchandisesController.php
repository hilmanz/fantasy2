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
		$this->loadModel('MerchandiseOrder');
		$start = intval(@$this->request->query['start']);
		$limit = 20;
		$this->MerchandiseItem->bindModel(
			array('belongsTo'=>array('MerchandiseCategory'))
		);
		$rs = $this->MerchandiseItem->find('all',array('offset'=>$start,'limit'=>$limit));
		
		//for each items, we need to calculate its stock availability.
		//so we need the total purchased item so far each.
		for($i=0; $i<sizeof($rs);$i++){
			$total_item = $this->MerchandiseOrder->find('count',
											array('conditions'=>
											array('merchandise_item_id'=>$rs[$i]['MerchandiseItem']['id'],
													'n_status <> 4'))
											);
			$available_item = $rs[$i]['MerchandiseItem']['stock'] - $total_item;
			$rs[$i]['stock'] = $available_item;
		}


		$this->set('response',array('status'=>1,'data'=>$rs,'next_offset'=>$start+$limit,'rows_per_page'=>$limit));
		$this->render('response');
		
	}
	public function edit($id){
		$this->loadModel('MerchandiseItem');
		$this->loadModel('MerchandiseOrder');
		
		$rs = $this->MerchandiseItem->findById($id);

		

		if($this->request->is('post')){
			$this->MerchandiseItem->id = $id;
			
			//add stock with additional new stock.
			$this->request->data['stock'] = $rs['MerchandiseItem']['stock'] + 
											intval($this->request->data['new_stock']);



			
			if(isset($_FILES['pic']['name'])){
				$this->update_pic($id);
			}
			$this->Session->setFlash('Update Completfed !');
			$this->MerchandiseItem->save($this->request->data);
			$this->redirect('/merchandises/');
		}
		

		$this->loadModel('MasterPerk');
		$perks = $this->MasterPerk->find('all',array('limit'=>300));
		$this->set('perks',$perks);

		$this->set('rs',$rs);
		
		$this->loadModel('MerchandiseCategory');
		$categories = $this->MerchandiseCategory->find('all',array('limit'=>100));
		$this->set('categories',$categories);


	}
	/*
	* database ongkos kirim 
	* saat ini ongkos kirim kita pakai JNE Reguler
	*/
	public function ongkir(){

	}
	/*
	* ajax call for getting the list of delivery cost.
	*/
	public function get_ongkir(){
		$this->loadModel('Ongkir');
		$this->layout = 'ajax';
		$start = intval($this->request->query['start']);

		$rs = $this->Ongkir->find('all',array(
				'limit'=>100,
				'offset'=>$start,
			));

		$this->set('response',array('status'=>1,'data'=>$rs,'next_offset'=>$start+100));
		$this->render('response');
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

		$this->loadModel('MasterPerk');
		$perks = $this->MasterPerk->find('all',array('limit'=>300));
		$this->set('perks',$perks);

	}
	public function view_ongkir(){
		
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
	public function view_order($order_id){
		$this->loadModel('MerchandiseOrder');
		$this->loadModel('Ongkir');
		if($this->request->is('post')){
			if($this->request->data['n_status']==4){
				if($this->refund($order_id)){
					$this->restock($order_id);
					$this->update_order($order_id);
				}else{
					$this->Session->setFlash('cannot update the order, please try again later !');
				}
			}else{
				$this->update_order($order_id);
			}
		}

		$this->MerchandiseOrder->bindModel(
			array('belongsTo'=>array('MerchandiseItem'))
		);
		$rs = $this->MerchandiseOrder->findById($order_id);
		//get ongkir
		$ongkir = $this->Ongkir->findById($rs['MerchandiseOrder']['ongkir_id']);
		$this->set('ongkir',$ongkir['Ongkir']);
		$this->set('rs',$rs);
	}
	private function restock($order_id){
		$this->loadModel('MerchandiseItem');
		$this->MerchandiseOrder->id = $order_id;
		$this->MerchandiseOrder->bindModel(
			array('belongsTo'=>array('MerchandiseItem'))
		);
		/*
		temporarily disabled these flow.
		there's a bug when people has been canceled, and the order were more than the initial stock number.
		the stock is increased while there's already n items delivered.
		
		$order = $this->MerchandiseOrder->findById($order_id);
		$this->MerchandiseItem->id = $order['MerchandiseItem']['id'];
		$this->MerchandiseItem->save(array('stock'=>$order['MerchandiseItem']['stock'] + 1));
		*/
	}
	private function update_order($order_id){
		$this->MerchandiseOrder->id = $order_id;
		$rs = $this->MerchandiseOrder->save($this->request->data);
		if($rs){
			
			$this->Session->setFlash('the order has been updated successfully !');
		}else{
			$this->Session->setFlash('cannot update the order, please try again later !');
		}
	}
	public function get_orders(){
		$this->layout = 'ajax';
		$this->loadModel('MerchandiseOrder');
		$start = intval(@$this->request->query['start']);
		$limit = 20;
		$this->MerchandiseOrder->bindModel(
			array('belongsTo'=>array('MerchandiseItem'))
		);
		if(!isset($this->request->query['status'])){
			$n_status = array(0,1,2,3,4);
		}else{
			$n_status= $this->request->query['status'];
		}
		$rs = $this->MerchandiseOrder->find('all',
											array('conditions'=>array(
														'MerchandiseOrder.n_status'=>$n_status
												  ),
												  'offset'=>$start,
												  'limit'=>$limit));
		for($i=0; $i<sizeof($rs); $i++){
			if($rs[$i]['MerchandiseOrder']['data']!=null){
				$rs[$i]['MerchandiseOrder']['data'] = unserialize($rs[$i]['MerchandiseOrder']['data']);
			}
		}
		$this->set('response',array('status'=>1,'data'=>$rs,'next_offset'=>$start+$limit,'rows_per_page'=>$limit));
		$this->render('response');
	}
	private function refund($order_id){
		$refund_ok = false;
		$this->MerchandiseOrder->bindModel(
			array('belongsTo'=>array('MerchandiseItem'))
		);
		$order = $this->MerchandiseOrder->findById($order_id);

		//make sure that the transaction is not yet canceled
		if($order['MerchandiseOrder']['n_status']!=4){
			if($order['MerchandiseOrder']['order_type']==0){
				$refund_ok = $this->refund_game_funds($order);	
			}else if($order['MerchandiseOrder']['order_type']==1){

				$refund_ok = $this->refund_game_cash($order);	
			}else{
				$refund_ok = true;
			}
			
		}

		return $refund_ok;
	}

	private function refund_game_cash($order){
		$refund_ok = false;
		//make sure that the deducted fund is exists
		$statement = $this->getCashPurchaseStatement($order['MerchandiseOrder']['game_team_id'],
										$order['MerchandiseOrder']['po_number']);
			
		if(intval($statement['id']) > 0){
			$refund_amount = intval($statement['amount']) * -1;
			//if everything is fine, then we process the refund
			$rs = $this->Game->query("
				INSERT IGNORE INTO ffgame.game_transactions
				(game_team_id,transaction_name,transaction_dt,
				 amount,details)
				VALUES
				({$order['MerchandiseOrder']['game_team_id']},
				  'purchase_{$order['MerchandiseOrder']['po_number']} - refunded',
				  NOW(),
				  {$refund_amount},
				  'purchase merchandise - {$order['MerchandiseOrder']['po_number']} - refunded'
				  );",false);
			//then set n_status of order to 4 (canceled)
			$refund_stmt = $this->getCashRefundedStatement($order['MerchandiseOrder']['game_team_id'],
										$order['MerchandiseOrder']['po_number']);

			//recount the cash
			$this->refresh_cash($order['MerchandiseOrder']['game_team_id']);

			if(intval($refund_stmt['id']) > 0){
				$refund_ok = true;	
			}
		}
		return $refund_ok;
	}
	private function refresh_cash($game_team_id){
		$sql = "INSERT INTO ffgame.game_team_cash
				(game_team_id,cash)
				SELECT game_team_id,SUM(amount) AS cash 
				FROM ffgame.game_transactions
				WHERE game_team_id = {$game_team_id}
				GROUP BY game_team_id
				ON DUPLICATE KEY UPDATE
				cash = VALUES(cash);";
		$this->Game->query($sql,false);
	}
	private function refund_game_funds($order){
		$refund_ok = false;
		//make sure that the deducted fund is exists
		$statement = $this->getPurchaseStatement($order['MerchandiseOrder']['game_team_id'],
										$order['MerchandiseOrder']['po_number']);
			
		if(intval($statement['id']) > 0){
			//if everything is fine, then we process the refund
			$rs = $this->Game->query("
				INSERT IGNORE INTO ffgame.game_team_expenditures
				(game_team_id,item_name,item_type,
				 amount,game_id,match_day,item_total,base_price)
				VALUES
				({$order['MerchandiseOrder']['game_team_id']},
				  'purchase merchandise - {$order['MerchandiseOrder']['po_number']} - refunded',
				  1,
				  {$order['MerchandiseItem']['price_currency']},
				  '{$statement['game_id']}',
				  {$statement['match_day']},1,1);",false);
			//then set n_status of order to 4 (canceled)
			$refund_stmt = $this->getRefundedStatement($order['MerchandiseOrder']['game_team_id'],
										$order['MerchandiseOrder']['po_number']);
			if(intval($refund_stmt['id']) > 0){
				$refund_ok = true;	
			}
		}
		return $refund_ok;
	}
	private function getPurchaseStatement($game_team_id,$po_number){
		$sql = "SELECT * FROM ffgame.game_team_expenditures a
				WHERE game_team_id={$game_team_id} AND 
				item_name ='purchase merchandise - {$po_number}' LIMIT 1;";
		$rs = $this->Game->query($sql,false);
		if(sizeof($rs)>0){
			return $rs[0]['a'];
		}else{
			return array('id'=>0);
		}
	}
	private function getCashPurchaseStatement($game_team_id,$po_number){
		$sql = "SELECT * FROM ffgame.game_transactions a
				WHERE game_team_id={$game_team_id} AND 
				transaction_name ='purchase_{$po_number}' LIMIT 1;";
		
		$rs = $this->Game->query($sql,false);

		if(sizeof($rs)>0){
			return $rs[0]['a'];
		}else{
			return array('id'=>0);
		}
	}
	private function getCashRefundedStatement($game_team_id,$po_number){
		$sql = "SELECT * FROM ffgame.game_transactions a
				WHERE game_team_id={$game_team_id} AND 
				transaction_name ='purchase_{$po_number} - refunded' LIMIT 1;";
		$rs = $this->Game->query($sql,false);
		if(sizeof($rs)>0){
			return $rs[0]['a'];
		}else{
			return array('id'=>0);
		}
	}
	private function getRefundedStatement($game_team_id,$po_number){
		$sql = "SELECT * FROM ffgame.game_team_expenditures a
				WHERE game_team_id={$game_team_id} AND 
				item_name ='purchase merchandise - {$po_number} - refunded' LIMIT 1;";
		$rs = $this->Game->query($sql,false);
		if(sizeof($rs)>0){
			return $rs[0]['a'];
		}else{
			return array('id'=>0);
		}
	}
}