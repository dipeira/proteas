<div id="main-menu">
		<ul id="navigation">
			<li><a href="<?=SITE_ROOT?>/index.php">Αρχικη</a></li>
			
			<li class="sub">
				<a href="#">Πινακας</a>
				<ul>
					<li><a href="<?=SITE_ROOT?>/employee/ektaktoi_list.php">Έκτακτο Προσωπικό</a></li>
					<li><a href="<?=SITE_ROOT?>/employee/praxi.php">Διαχείριση πράξεων</a></li>
					<li><a href="<?=SITE_ROOT?>/employee/idiwtikoi.php">Ιδιωτικοί εκπ/κοί</a></li>
					<li><a href="<?=SITE_ROOT?>/school/school_edit.php">Επεξεργασία Σχολείου</a></li>
				</ul>
			</li>
			
			<li class="sub">
				<a href="#">Εργαλεια</a>
				<ul>
					<li><a href="<?=SITE_ROOT?>/school/school_status.php">Καρτέλα Σχολείου</a></li>
					<li><a href="<?=SITE_ROOT?>/employee/apof_ad.php">Αποφάσεις Αδειών</a></li>						
					<li><a href="<?=SITE_ROOT?>/etc/stats.php">Στατιστικά</a></li>
				</ul>
			</li>
		 
		 	<li class="sub">
		  	<a href="#">Αναφορες</a>
    		<ul>
		   		<li><a href="<?=SITE_ROOT?>/reports/report_tm_ekp.php">Μαθητές & Εκπ/κοί</a></li>
					<li><a href="<?=SITE_ROOT?>/reports/report_kena.php?type=1">Οργανικά Κενά</a></li>
					<li><a href="<?=SITE_ROOT?>/reports/report_leit.php">Λειτουργικά Κενά Δημοτικών</a></li>
					<li><a href="<?=SITE_ROOT?>/reports/report_tm_ekp.php?type=4">Λειτουργικά Κενά Νηπιαγωγείων</a></li>
					<li><a href="<?=SITE_ROOT?>/reports/report_leit_yp.php">Λειτουργικά Κενά (για Υπουργείο)</a></li>
					<!--<li><a href="report_leit.php">Λειτουργικά Κενά (από υπολογισμό)</a></li>-->
					<!--<li><a href="report_kena_eid.php?klados=3">Λειτουργ.Κενά ΠΕ06 & ΠΕ11</a></li>-->
					<!--<li><a href="report_kena_eid_eaep.php?klados=3">Λειτουργ.Κενά Ειδικοτήτων ΕΑΕΠ</a></li>-->
					<li><a href="<?=SITE_ROOT?>/employee/absents.php">Εκπ/κοί σε άδεια</a></li>
					<li><a href="<?=SITE_ROOT?>/etc/check_wres.php">Συμπλήρωση υποχρ.ωραρίου</a></li>
		  	</ul>
		 	</li>
                 
      <li class="sub">
		  	<a href="#">Αναζητηση</a>
      	<ul>
					<li><a href="<?=SITE_ROOT?>/employee/search.php">Προσωπικού</a></li>
					<li><a href="<?=SITE_ROOT?>/employee/search_adeia.php">Αδειών</a></li>
		  	</ul>
		 	</li>
		 <?php
		 if ($usrlvl == 0):
		 ?>
		 <li class="sub">
		  <a href="#">Διαχειριση</a>
		  <ul>
			  <li><a href="<?=SITE_ROOT?>/etc/end_of_year.php">Λήξη Διδακτικού Έτους - Ενέργειες</a></li>
			  <!--<li><a href="<?=SITE_ROOT?>/employee/check_vmk.php">Αλλαγές Βαθμών - ΜΚ</a></li>-->
			  <li><a href='<?=SITE_ROOT?>/etc/params.php'>Παράμετροι</a></li>
			  <li><a href="<?=SITE_ROOT?>/employee/klados.php">Ειδικότητες</a></li>
			  <li><a href='<?=SITE_ROOT?>/etc/users.php'>Διαχείριση Χρηστών</a></li>
			  <li><a href='<?=SITE_ROOT?>/etc/log.php'>Αρχείο καταγραφής συμβάντων</a></li>
		  </ul>
		 </li>
		 <?php endif; ?>
		 <li class="sub">
		  <a href="<?=SITE_ROOT?>/etc/about.php">Σχετικα</a>
		 </li>
		<li class="sub">
			<a href='<?=SITE_ROOT?>/tools/login.php?logout=1'>Eξοδος</a>
		</ul>
  
	</div>