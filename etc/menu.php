<div id="main-menu">
		<ul id="navigation">
			<li><a href="<?=SITE_ROOT?>/index.php">������</a></li>
			
			<li class="sub">
				<a href="#">�������</a>
				<ul>
					<li><a href="<?=SITE_ROOT?>/employee/ektaktoi_list.php">������� ���������</a></li>
					<li><a href="<?=SITE_ROOT?>/employee/praxi.php">���������� �������</a></li>
					<li><a href="<?=SITE_ROOT?>/employee/idiwtikoi.php">��������� ���/���</a></li>
					<li><a href="<?=SITE_ROOT?>/school/school_edit.php">����������� ��������</a></li>
				</ul>
			</li>
			
			<li class="sub">
				<a href="#">��������</a>
				<ul>
					<li><a href="<?=SITE_ROOT?>/school/school_status.php">������� ��������</a></li>
					<li><a href="<?=SITE_ROOT?>/employee/apof_ad.php">��������� ������</a></li>						
					<li><a href="<?=SITE_ROOT?>/etc/stats.php">����������</a></li>
				</ul>
			</li>
		 
		 	<li class="sub">
		  	<a href="#">��������</a>
    		<ul>
		   		<li><a href="<?=SITE_ROOT?>/reports/report_tm_ekp.php">������� & ���/���</a></li>
					<li><a href="<?=SITE_ROOT?>/reports/report_kena.php?type=1">�������� ����</a></li>
					<li><a href="<?=SITE_ROOT?>/reports/report_leit.php">����������� ���� ���������</a></li>
					<li><a href="<?=SITE_ROOT?>/reports/report_tm_ekp.php?type=4">����������� ���� ������������</a></li>
					<li><a href="<?=SITE_ROOT?>/reports/report_leit_yp.php">����������� ���� (��� ���������)</a></li>
					<!--<li><a href="report_leit.php">����������� ���� (��� ����������)</a></li>-->
					<!--<li><a href="report_kena_eid.php?klados=3">��������.���� ��06 & ��11</a></li>-->
					<!--<li><a href="report_kena_eid_eaep.php?klados=3">��������.���� ����������� ����</a></li>-->
					<li><a href="<?=SITE_ROOT?>/employee/absents.php">���/��� �� �����</a></li>
					<li><a href="<?=SITE_ROOT?>/etc/check_wres.php">���������� �����.�������</a></li>
		  	</ul>
		 	</li>
                 
      <li class="sub">
		  	<a href="#">���������</a>
      	<ul>
					<li><a href="<?=SITE_ROOT?>/employee/search.php">����������</a></li>
					<li><a href="<?=SITE_ROOT?>/employee/search_adeia.php">������</a></li>
		  	</ul>
		 	</li>
		 <?php
		 if ($usrlvl == 0):
		 ?>
		 <li class="sub">
		  <a href="#">����������</a>
		  <ul>
			  <li><a href="<?=SITE_ROOT?>/etc/end_of_year.php">���� ���������� ����� - ���������</a></li>
			  <!--<li><a href="<?=SITE_ROOT?>/employee/check_vmk.php">������� ������ - ��</a></li>-->
			  <li><a href='<?=SITE_ROOT?>/etc/params.php'>����������</a></li>
			  <li><a href="<?=SITE_ROOT?>/employee/klados.php">�����������</a></li>
			  <li><a href='<?=SITE_ROOT?>/etc/users.php'>���������� �������</a></li>
			  <li><a href='<?=SITE_ROOT?>/etc/log.php'>������ ���������� ���������</a></li>
		  </ul>
		 </li>
		 <?php endif; ?>
		 <li class="sub">
		  <a href="<?=SITE_ROOT?>/etc/about.php">�������</a>
		 </li>
		<li class="sub">
			<a href='<?=SITE_ROOT?>/tools/login.php?logout=1'>E�����</a>
		</ul>
  
	</div>