<?php

App::uses('AppController', 'Controller');
/*
* Manage Digital Coupon
* Pad
*/

class CouponController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Coupon';

	//display the available coupons, 20 items each
	public function index(){
		$this->paginate = array('limit'=>20,
								'order'=>array('Coupon.id'=>'desc')
								);
		$this->set('data',$this->Paginate('Coupon'));
	}
	public function view($coupon_id){
		$this->loadModel('CouponCode');
		$coupon = $this->Coupon->findById($coupon_id);
		$coupon_count = $this->CouponCode->find('count',
												array('conditions'=>array('coupon_id'=>$coupon_id)));
		$this->set('coupon',$coupon);
		$this->set('coupon_count',$coupon_count);
	}
	public function create(){
		if($this->request->is('post')){
			if(is_array($_FILES['img'])){
				$filename = 'voucher_'.Inflector::slug($_FILES['img']['name']);
				if(move_uploaded_file($_FILES['img']['tmp_name'],
									Configure::read('avatar_img_dir').$filename)){
					$this->request->data['Coupon']['img'] = $filename;
					$this->request->data['Coupon']['created_dt'] = date("Y-m-d H:i:s");
					$this->Coupon->create();
					$rs = $this->Coupon->save($this->request->data['Coupon']);
					if(isset($rs['Coupon']['id']) && $rs['Coupon']['id'] > 0){
						$this->Session->setFlash("New Coupon/Voucher has been created successfully !");
					}else{
						$this->Session->setFlash("Please upload the Coupon/Voucher image !");
					}
				}else{
					$this->Session->setFlash("Please upload the Coupon/Voucher image !");
				}
			}
		}

	}
	public function download($coupon_id){
		$info = $this->Coupon->findById($coupon_id);

		// The user will receive a PDF to download
		header('Content-type: plain/text');
		// File will be called downloaded.pdf
		header('Content-Disposition: attachment; filename="'.
					Inflector::slug($info['Coupon']['vendor_name']).'-'.
					$info['Coupon']['id'].
					'-'.
					date("Ymdhis").
					'.csv"');


		$this->loadModel('CouponCode');
		$start = 0;

		while(1){
			$coupon = $this->CouponCode->find('all',array(
				'conditions'=>array(
					'coupon_id'=>$coupon_id,
					'n_status'=>0,
					'paid'=>0
				),
				'start'=>$start,
				'limit'=>100
			));
			$start+=100;
			if(sizeof($coupon)==0){
				break;
			}
			for($i=0;$i<sizeof($coupon);$i++){
				print $coupon[$i]['CouponCode']['coupon_code'].PHP_EOL;
			}
		}
		die();
	}
	/*
	* page for generating unique voucher codes
	*/
	public function generate($id){
		$coupon = $this->Coupon->findById($id);
		$this->set('coupon',$coupon);

	}
	public function ajax_generate($id){
		$n_total = intval($this->request->query['total']);
		$n_length = strlen($id);
		$sql = "";
		for($i=0;$i<$n_total;$i++){
			if($sql==""){
				$sql.="INSERT INTO coupon_codes
									(coupon_id,coupon_code,created_dt,redeem_dt,game_team_id,n_status)
									VALUES";
			}
			set_time_limit(30);
			while(1){
				$code = $id.rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).
					rand(0,9).rand(0,9).rand(0,9).rand(0,9);
				$rs = $this->Coupon->query("SELECT id FROM coupon_codes 
										WHERE coupon_code = '{$code}' LIMIT 1");
				if(sizeof($rs)==0){
					if($i > 0){
						$sql.=",";
					}
					$sql.="({$id},{$code},NOW(),NULL,0,0)";
					break;
				}
			}
		}

		$this->layout = "ajax";
		

		if(strlen($sql)>0){
			$this->Coupon->query($sql,false);
			$this->set('response',array('status'=>1,'sql'=>$sql));
		}else{
			$this->set('response',array('status'=>0));
		}
		
		
		$this->render('response');
	}
}
