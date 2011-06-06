 <script type="text/javascript">
 var datii = <?=$datii ?>
 var line = new RGraph.Line("myLine", datii);
    line.Set('chart.background.barcolor1', '<?=$background_bar_colour_1?>');
    line.Set('chart.background.barcolor2', '<?=$background_bar_colour_2?>');
    line.Set('chart.background.grid.color','<?=$background_grid_colour?>');
    line.Set('chart.colors', "<?=$colours?>");
    line.Set('chart.linewidth', <?=$line_width?>);
    line.Set('chart.filled', <?=$filled?>);
    line.Set('chart.hmargin', <?=$horizontal_margin?>);
    line.Set('chart.labels', "<?=$labels?>");
    line.Set('chart.gutter.left', "<?=$left_gutter?>");
    line.Draw();
 </script>

