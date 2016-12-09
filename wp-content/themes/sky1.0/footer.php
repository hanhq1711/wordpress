</section>
<div class="branding branding-black">
    <div class="container_f">
	 <?php if( dopt('d_footcode_a') ) echo dopt('d_footcode_1'); ?>
    </div>
</div>
<footer class="footer">
<div class="footer-inner">
  <p>
    <?php if( dopt('d_footcode_b') ) echo dopt('d_footcode'); ?>&nbsp;&nbsp;<?php if( dopt('d_track_b') ) echo dopt('d_track'); ?>
  </p>
  <p>Powered by 韩狼<a title="韩狼Blog" href="http://www.hhq1711.com">韩狼Blog</a> </p>
</div>

</footer>
<?php 
wp_footer(); 
global $dHasShare; 
if($dHasShare == true){ 
	echo'<script>with(document)0[(getElementsByTagName("head")[0]||body).appendChild(createElement("script")).src="http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion="+~(-new Date()/36e5)];</script>';
}
?>

<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?b6a5503b5e70ed8d54e977378ec0d39d";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>

</body></html>