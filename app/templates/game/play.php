<div class='manolito'></div>
<div class='ipads'><br/></div>
<form class='questions' action='' method='post' name='play_form'>
    <legend>Pop Quiz</legend>
<?
for ($x=0; $x < count($questions), $q=@$questions[$x]; $x++){
        echo "<br/>";
    echo "<div class='question'><p>".($x+1) . ' ' . $q['question']."</p>";
    echo "<ul>";
    foreach ($q['options'] as $choice) {
        echo "<li><input name='answers[]' type='checkbox' value='".$choice."'/> ".$choice."</li>";
    }
    echo "</ul></div>";
}
?>
<input type='hidden' name='time' value='<?=$time?>'/>
<input style='height:20px;width:50px;margin:20px;font-size:20px' type='submit' name='submit' value=' OK '/>
</form>
