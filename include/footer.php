<noscript><h1>Please enable JavaScript.</h1></noscript>
<div class="text-center">
<?php
$c= "2024";
if ($c == date("Y")){
?>
<p>&copy; IVAO Israel <?php echo date("Y");?></p>
<?php } elseif ($c < date("Y")){
?>
<p>&copy; IVAO Israel <?php echo $c ."-" .date("Y");?></p>
<?php }?>
</div>