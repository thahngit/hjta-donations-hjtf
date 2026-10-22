<?php  


$_root = $_SERVER['DOCUMENT_ROOT'];

  // REAL vs FAKE setup
  $whereami = trim(getcwd()) ;
  if ( strtoupper(substr($whereami,-5)) == '_FAKE'  ) :
//     require_once($_root."/DataReply/HJTA_SUBDOM/common/Z_HJTFglobals_FAKE.php");
//     require_once($_root."/DataReply/HJTA_SUBDOM/common/connect_FAKE.php");
     require_once($_root."/common/Z_HJTFglobals_FAKE.php");
     require_once($_root."/common/connect_FAKE.php");
  else :
//      require_once($_root."/DataReply/HJTA_SUBDOM/common/Z_HJTFglobals.php");
//      require_once($_root."/DataReply/HJTA_SUBDOM/common/connect.php");
      require_once($_root."/common/Z_HJTFglobals.php");
      require_once($_root."/common/connect.php");
  endif ;

?>



<html>
<head>
	<title>Donate Now! | Howard Jarvis Taxpayers Foundation</title>
</head>
<body topmargin="20" leftmargin="20" marginheight="20" marginwidth="20" style="font-family:Courier New;">
<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-NQP3DX"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NQP3DX');</script>
<!-- End Google Tag Manager -->

	<table cellspacing="0" cellpadding="0" width="469" border="0">
		<tbody>
			<tr>
				<td width="100%" style="font-family: 'Courier New', Courier, sans-serif; font-size: 14px;">
					<h1 style="font-family: Arial, Helvetica, sans-serif; color: #000066; font-size:24px; line-height: 28px;">Howard Jarvis Tax Foundation (HJTF) Mail-in Donation Form</h1>
					<p style="font-family:Arial,Helvetica;">Please print this form, fill it out and mail it in along with your check.</p>
					<p style="font-family:Arial,Helvetica;"><br>
					<strong>Contact Information:</strong></p>
					<p>Name:__________________________________________</p>
					<p>Address:_______________________________________</p>

					<p>City:_________________ State:____ Zip:_________</p>
					<p>Phone:_______________________________(optional)</p>
					<p style="font-family:Arial,Helvetica;"><br><strong>Add your e-mail address to subscribe to HJTA updates:</strong></p>
					<p>E-Mail:______________________________</p>
					<p><br><strong>Amount Enclosed: &nbsp;&nbsp;$________</strong><br>
					<p style="font-family:Arial,Helvetica;"><br><strong>Please Mail To:</strong></p>
					<p>Howard Jarvis Taxpayers Foundation<br>
					621 South Westmoreland Avenue, Suite 200<br>
					Los Angeles, CA 90005</p>
					<p style="font-family:Arial,Helvetica;"><br>*Contributions or gifts to the Howard Jarvis Taxpayers Foundation are deductible from your federal income tax and may also be deductible from your state income tax.
 <br/><br/>
Howard Jarvis Taxpayers Foundation Tax ID: 52-1155794</p>
				</td>
			</tr>
		</tbody>
	</table>
</body>
</html>
