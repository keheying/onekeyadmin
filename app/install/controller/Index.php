<?php
// +----------------------------------------------------------------------
// | OneKeyAdmin [ Believe that you can do better ]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2023 http://onekeyadmin.com All rights resulterved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MUKE <513038996@qq.com>
// +----------------------------------------------------------------------
namespace app\install\controller;

use PDO;
use onekey\File;
use think\facade\Db;
use think\facade\View;
use app\install\BaseController;
/**
 * 安装页面
 */
class Index extends BaseController
{
	/**
	 * 安装
	 */
    public function index()
    {
        if ($this->request->isPost()) {
        	$token = session('token');
        	if (empty($token)) {
        		$result = api_post('tokenSystem/install', $_POST);
        		$token  = $result['status'] === 'success' ? $result['token'] : '';
        		session('token', $token);
        	}
        	if ($_POST['admin_url'] == 'admin') {
        	    return json(['status' => 'error', 'message' => '管理系统地址,请不要用admin']);
        	}
        	if (empty($token)) {
        		return json(['status' => 'error', 'message' => 'token获取失败！']);
        	}
            $hostname = "localhost";
            $adminmap = $_POST['admin_url'];
			$username = $_POST['sql_username'];
			$password = $_POST['sql_password'];
			$database = $_POST['sql_database'];
			$data = date('Y-m-d H:i:s');
			$dsn  = "mysql:dbname=$database;host=$hostname";
			try {
				// 创建数据库
			    $connect = new PDO("mysql:host=$hostname", $username, $password);
			    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			    $create = "CREATE DATABASE $database DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_general_ci";
			    $connect->exec($create);
			    // 创建数据表
			    $db  = new PDO($dsn, $username, $password);
			    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
			    $sql = file_get_contents(root_path() . 'install.sql');
			    $db->exec($sql);
			    // 初始化数据
			    $db->exec("insert into `mk_admin`(`id`,`admin_id`,`group_id`,`nickname`,`email`,`account`,`password`,`cover`,`login_ip`,`login_count`,`login_time`,`create_time`,`status`) values (1,0,1,'".$_POST['name']."','".$_POST['email']."','".$_POST['admin_account']."','".password_hash($_POST['admin_password'], PASSWORD_BCRYPT, ['cost' => 12])."','','".$this->request->ip()."',0,'".$data."','".$data."',1)");
			    // 初始化文件
			    $init = file_put_contents(root_path() . '.env',"APP_DEBUG = false\nAPP_TOKEN = $token\nMAP_ADMIN = $adminmap\n\n[DATABASE]\nHOSTNAME = localhost\nDATABASE = $database\nUSERNAME = $username\nPASSWORD = $password");
			    // 删除安装包
			    File::delDirAndFile(app_path());
			    return json(['status' => 'success', 'message' => '安装成功']);
			} catch(\PDOException $e) {
			    return json(['status' => 'error', 'message' => $e->getMessage()]);
			}
        } else {
	        // 修改底层（兼容localhost）
	        $file = $this->request->baseFile();
	        if ($file && 0 !== strpos($this->request->url(), $file)) {
	            $file = str_replace('\\', '/', dirname($file));
	        }
	        $this->request->root = rtrim($file, '/');
        	View::assign([
        		'licenses' => $this->licenses(),
        		'domain'   => $this->request->domain() . $this->request->root,
        		'active'   => is_file(root_path() . '.env') ? 1 : 0,
        	]);
            return View::fetch();
        }
    }

    /**
	 * 协议
	 */
    public function licenses()
    {
    	return '<div style="height: 500px;overflow: auto;margin: 3em 10%; font-family:serif;text-align:justify; border: 1px solid black; padding: 2em;   box-shadow: 4px 8px #888888;">
		<p style="text-align:center">
		<strong>Apache License<br>Version 2.0, January 2004<br></strong>
		<strong><a href="http://www.apache.org/licenses/">http://www.apache.org/licenses/</a></strong>
		</p>
		<p>TERMS AND CONDITIONS FOR USE, REPRODUCTION, AND DISTRIBUTION</p>
		<p><strong><a name="definitions">1. Definitions</a></strong>.</p>
		<div style="margin-left: 1em;">
		<p>"<strong>License</strong>" shall mean the terms and conditions for use, reproduction, and
		distribution as defined by Sections 1 through 9 of this document.</p>
		<p>"<strong>Licensor</strong>" shall mean the copyright owner or entity authorized by the
		copyright owner that is granting the License.</p>
		<p>"<strong>Legal Entity</strong>" shall mean the union of the acting entity and all other
		entities that control, are controlled by, or are under common control with
		that entity. For the purposes of this definition, "<strong>control</strong>" means (i) the
		power, direct or indirect, to cause the direction or management of such
		entity, whether by contract or otherwise, or (ii) ownership of fifty
		percent (50%) or more of the outstanding shares, or (iii) beneficial
		ownership of such entity.</p>
		<p>"<strong>You</strong>" (or "<strong>Your</strong>") shall mean an individual or Legal Entity exercising
		permissions granted by this License.</p>
		<p>"<strong>Source</strong>" form shall mean the preferred form for making modifications,
		including but not limited to software source code, documentation source,
		and configuration files.</p>
		<p>"<strong>Object</strong>" form shall mean any form resulting from mechanical transformation
		or translation of a Source form, including but not limited to compiled
		object code, generated documentation, and conversions to other media types.</p>
		<p>"<strong>Work</strong>" shall mean the work of authorship, whether in Source or Object form,
		made available under the License, as indicated by a copyright notice that
		is included in or attached to the work (an example is provided in the
		Appendix below).</p>
		<p>"<strong>Derivative Works</strong>" shall mean any work, whether in Source or Object form,
		that is based on (or derived from) the Work and for which the editorial
		revisions, annotations, elaborations, or other modifications represent, as
		a whole, an original work of authorship. For the purposes of this License,
		Derivative Works shall not include works that remain separable from, or
		merely link (or bind by name) to the interfaces of, the Work and Derivative
		Works thereof.</p>
		<p>"<strong>Contribution</strong>" shall mean any work of authorship, including the original
		version of the Work and any modifications or additions to that Work or
		Derivative Works thereof, that is intentionally submitted to Licensor for
		inclusion in the Work by the copyright owner or by an individual or Legal
		Entity authorized to submit on behalf of the copyright owner. For the
		purposes of this definition, "<strong>submitted</strong>" means any form of electronic,
		verbal, or written communication sent to the Licensor or its
		representatives, including but not limited to communication on electronic
		mailing lists, source code control systems, and issue tracking systems that
		are managed by, or on behalf of, the Licensor for the purpose of discussing
		and improving the Work, but excluding communication that is conspicuously
		marked or otherwise designated in writing by the copyright owner as "<strong>Not a
		Contribution.</strong>"</p>
		<p>"<strong>Contributor</strong>" shall mean Licensor and any individual or Legal Entity on
		behalf of whom a Contribution has been received by Licensor and
		subsequently incorporated within the Work.</p>
		</div>
		<p><strong><a name="copyright">2. Grant of Copyright License</a></strong>. Subject to the
		terms and conditions of this License, each Contributor hereby grants to You
		a perpetual, worldwide, non-exclusive, no-charge, royalty-free, irrevocable
		copyright license to reproduce, prepare Derivative Works of, publicly
		display, publicly perform, sublicense, and distribute the Work and such
		Derivative Works in Source or Object form.</p>
		<p><strong><a name="patent">3. Grant of Patent License</a></strong>. Subject to the terms
		and conditions of this License, each Contributor hereby grants to You a
		perpetual, worldwide, non-exclusive, no-charge, royalty-free, irrevocable
		(except as stated in this section) patent license to make, have made, use,
		offer to sell, sell, import, and otherwise transfer the Work, where such
		license applies only to those patent claims licensable by such Contributor
		that are necessarily infringed by their Contribution(s) alone or by
		combination of their Contribution(s) with the Work to which such
		Contribution(s) was submitted. If You institute patent litigation against
		any entity (including a cross-claim or counterclaim in a lawsuit) alleging
		that the Work or a Contribution incorporated within the Work constitutes
		direct or contributory patent infringement, then any patent licenses
		granted to You under this License for that Work shall terminate as of the
		date such litigation is filed.</p>
		<p><strong><a name="redistribution">4. Redistribution</a></strong>. You may reproduce and
		distribute copies of the Work or Derivative Works thereof in any medium,
		with or without modifications, and in Source or Object form, provided that
		You meet the following conditions:</p>
		<ol style="list-style: lower-latin;">
		<li>You must give any other recipients of the Work or Derivative Works a
		copy of this License; and</li>
		<li>You must cause any modified files to carry prominent notices stating
		that You changed the files; and</li>
		<li>You must retain, in the Source form of any Derivative Works that You
		distribute, all copyright, patent, trademark, and attribution notices from
		the Source form of the Work, excluding those notices that do not pertain to
		any part of the Derivative Works; and</li>
		<li>If the Work includes a "<strong>NOTICE</strong>" text file as part of its distribution,
		then any Derivative Works that You distribute must include a readable copy
		of the attribution notices contained within such NOTICE file, excluding
		those notices that do not pertain to any part of the Derivative Works, in
		at least one of the following places: within a NOTICE text file distributed
		as part of the Derivative Works; within the Source form or documentation,
		if provided along with the Derivative Works; or, within a display generated
		by the Derivative Works, if and wherever such third-party notices normally
		appear. The contents of the NOTICE file are for informational purposes only
		and do not modify the License. You may add Your own attribution notices
		within Derivative Works that You distribute, alongside or as an addendum to
		the NOTICE text from the Work, provided that such additional attribution
		notices cannot be construed as modifying the License.
		<br>
		<br>
		You may add Your own copyright statement to Your modifications and may
		provide additional or different license terms and conditions for use,
		reproduction, or distribution of Your modifications, or for any such
		Derivative Works as a whole, provided Your use, reproduction, and
		distribution of the Work otherwise complies with the conditions stated in
		this License.
		</li>
		</ol>
		<p><strong><a name="contributions">5. Submission of Contributions</a></strong>. Unless You
		explicitly state otherwise, any Contribution intentionally submitted for
		inclusion in the Work by You to the Licensor shall be under the terms and
		conditions of this License, without any additional terms or conditions.
		Notwithstanding the above, nothing herein shall supersede or modify the
		terms of any separate license agreement you may have executed with Licensor
		regarding such Contributions.</p>
		<p><strong><a name="trademarks">6. Trademarks</a></strong>. This License does not grant
		permission to use the trade names, trademarks, service marks, or product
		names of the Licensor, except as required for reasonable and customary use
		in describing the origin of the Work and reproducing the content of the
		NOTICE file.</p>
		<p><strong><a name="no-warranty">7. Disclaimer of Warranty</a></strong>. Unless required by
		applicable law or agreed to in writing, Licensor provides the Work (and
		each Contributor provides its Contributions) on an "AS IS" BASIS, WITHOUT
		WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied, including,
		without limitation, any warranties or conditions of TITLE,
		NON-INFRINGEMENT, MERCHANTABILITY, or FITNESS FOR A PARTICULAR PURPOSE. You
		are solely responsible for determining the appropriateness of using or
		redistributing the Work and assume any risks associated with Your exercise
		of permissions under this License.</p>
		<p><strong><a name="no-liability">8. Limitation of Liability</a></strong>. In no event and
		under no legal theory, whether in tort (including negligence), contract, or
		otherwise, unless required by applicable law (such as deliberate and
		grossly negligent acts) or agreed to in writing, shall any Contributor be
		liable to You for damages, including any direct, indirect, special,
		incidental, or consequential damages of any character arising as a result
		of this License or out of the use or inability to use the Work (including
		but not limited to damages for loss of goodwill, work stoppage, computer
		failure or malfunction, or any and all other commercial damages or losses),
		even if such Contributor has been advised of the possibility of such
		damages.</p>
		<p><strong><a name="additional">9. Accepting Warranty or Additional Liability</a></strong>.
		While redistributing the Work or Derivative Works thereof, You may choose
		to offer, and charge a fee for, acceptance of support, warranty, indemnity,
		or other liability obligations and/or rights consistent with this License.
		However, in accepting such obligations, You may act only on Your own behalf
		and on Your sole responsibility, not on behalf of any other Contributor,
		and only if You agree to indemnify, defend, and hold each Contributor
		harmless for any liability incurred by, or claims asserted against, such
		Contributor by reason of your accepting any such warranty or additional
		liability.</p>
		<p>END OF TERMS AND CONDITIONS</p>
		</div>';
    }
}
