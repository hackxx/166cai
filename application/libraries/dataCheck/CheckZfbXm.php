<?php
/**
 * 汇聚无限支付宝h5
 * @author Administrator
 *
 */
class CheckZfbXm
{
	private $CI;
	private $config;
	private $logPath = 'application/logs/dataCheck/';
	public function __construct()
	{
            $this->CI = &get_instance();
            $this->CI->load->model('other_data_check_model', 'dataModel');
	}
	
	public function exec($config, $r_flag = 0)
	{
		$this->config = $config;
		$checkTime = ($config['exc_date'] == '0000-00-00') ? strtotime("-1 day") : strtotime($config['exc_date']) + 86400;
                $checkDate = date('Y-m-d', $checkTime);
		$name = explode('-', $config['name']);
		$locFile = $this->logPath . date('Ymd', $checkTime) . '_xmzfb_' . $name[2] . '.txt';
		//走充值验签获取对账信息
		$c_type = explode(',', $this->config['c_type']);
		$configId = $c_type[0];
		require_once APPPATH . '/libraries/recharge/XmPay.php';
		$extra = $this->CI->dataModel->getPayConfig($configId);
		$extra = json_decode($extra['extra'], true);
		$paySubmit = new XmPay($extra);
		$rparams = array(
			'bill_date' => date('Ymd', $checkTime),
		);
		$respone = $paySubmit->queryBill($rparams);
		if(empty($respone['code']))
		{
			$this->CI->dataModel->updateCheckConfig($config['id'], array('fail_times' => $config['fail_times'] + 1));
			if(($config['fail_times'] + 1) >= 3)
			{
				$this->CI->dataModel->insertAlert($config['name'] . '_' . $checkDate, '对账单获取失败报警', $config['name'] . '充值对账单获取失败');
			}
				
			return ;
		}
		
		//将第三方数据保存文件
                $file = file_get_contents($respone['data']);
		file_put_contents($locFile, $file);
		unset($respone);
                
		if(!file_exists($locFile))
		{
			return ;
		}
		//解析对账文件
		$spl_object = new SplFileObject($locFile, 'r');
		if($spl_object)
		{
			$spl_object->seek(filesize($locFile));
			$total = $spl_object->key();
			if($total <= 1)
			{
				$this->CI->dataModel->insertAlert($config['name'] . '_gs_' . $checkDate, '对账单格式有误报警', $config['name'] . '充值对账单格式有误');
				return;
			}
			$pSize = 3000;
			$pages = ceil($total/ $pSize);
			$start = 1;
			$trunResult = $this->CI->dataModel->truncateRechargeCheck();
			if(!$trunResult)
			{
				return ;
			}
			$this->CI->dataModel->trans_start('db');
			for($i = 1; $i <= $pages; $i++)
			{
				$datas = array();
				$spl_object->seek($start);
				$num = $pSize;
				while ($num-- && !$spl_object->eof())
				{
					$dataStr = $spl_object->current();
					$data = explode('|', str_replace('||', '', $dataStr));
					if(($data['2'] != '1101'))
					{
						$spl_object->next();
						continue;
					}
					$datas[] = array('trade_no' =>trim($data['3']), 'date' => $checkDate, 'o_status' => 1, 'o_money' => trim($data['5']) * 100);
					$spl_object->next();
				}
				$start += $pSize;
				
				if($datas)
				{
					$res = $this->CI->dataModel->insertRechargeCheck($datas, array('trade_no', 'date', 'o_status', 'o_money'));
					if(!$res)
					{
						$this->CI->dataModel->trans_rollback('db');
						return ;
					}
				}
			}
			//执行比对操作
			$result = $this->CI->dataModel->checkRecharge($checkDate, $this->config, $r_flag);
			if(!$result)
			{
				$this->CI->dataModel->trans_rollback('db');
				return ;
			}
			$this->CI->dataModel->trans_complete('db');
		}
		else
		{
			$this->CI->dataModel->insertAlert($config['name'] . '_gs_' . $checkDate, '对账单格式有误报警', $config['name'] . '充值对账单格式有误');
		}
	}
}