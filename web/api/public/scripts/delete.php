<html>
<head>

<style>

.head_mini_gray {
	height: 30px;
	width: 600px;

    background-image:url(banner_mini_gray.png);
 
}


</style>

</head>
<body>	


<center>
<br>
<div class="head_mini_gray"></div>
<br>

<?php 
if ( !isset($_GET['key']) ) {
echo "service unavailable";
echo "</center>";
exit;	
} 

else {
$key=$_GET['key'] ;
?>


<form    action="delete_valide.php"  method="POST">
<input type="hidden"  name="key"  value="<?php echo $key ?>"   >
<input type="submit" style='background-color:red;color:white'  value="Cliquez pour vous  désinscrire">
</form>
<?php } ?>
  
</center>

</body>

</html>



