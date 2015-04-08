<?php
class Utils
{
	/**
	 * 获取随机数
	 * @param number $length 随机数长度
	 * @param number $type 随机数类型 1.大小写字母和数字 2.大小写字母 3.大写字母 4.小写字母 5.数字
	 * @return string 返回的随机数
	 */
	public static function getRandomKeys($length, $type = 1) {
		switch($type) {
			//大小写字母和数字
			case 1:
				$pattern = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
				break;
				//大小写字母
			case 2:
				$pattern = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
				break;
				//大写字母
			case 3:
				$pattern = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
				//小写字母
			case 4:
				$pattern = 'abcdefghijklmnopqrstuvwxyz';
				break;
				//数字
			case 5:
				$pattern = '1234567890';
				break;
			default:
				$pattern = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
				break;
		}
		$keys = '';
		for($i=0; $i<$length; $i++) {
			$keys .= $pattern{mt_rand(0, strlen($pattern)-1)};
		}
		return $keys;
	}
	
	/**
	 * 加密
	 * @param string $txt
	 * @return string
	 */
	public static function encryptCode($txt) {
		srand((double)microtime() * 1000000);
		$encrypt_key = md5(rand(0, 32000));
		$ctr = 0;
		$tmp = '';
		for($i = 0;$i < strlen($txt); $i++) {
			$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
			$tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
		}
		return base64_encode(self::passport_key($tmp));
	}
	
	/**
	 * 解密
	 * @param string $txt
	 * @return string
	 */
	public static function decryptCode($txt) {
		$txt = self::passport_key(base64_decode($txt));
		$tmp = '';
		for($i = 0;$i < strlen($txt); $i++) {
			$md5 = $txt[$i];
			$tmp .= $txt[++$i] ^ $md5;
		}
		return $tmp;
	}
	
	public static function passport_key($txt, $encrypt_key = 'howa.com.cn') {
		$encrypt_key = md5($encrypt_key);
		$ctr = 0;
		$tmp = '';
		for($i = 0; $i < strlen($txt); $i++) {
			$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
			$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
		}
		return $tmp;
	}
}