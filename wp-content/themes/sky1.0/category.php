<?php get_header(); ?>
<div class="content-wrap">
	<div class="content">
		<header class="archive-header"> 
			<h1><i class="fa fa-folder-open"></i>  &nbsp;分类：<?php single_cat_title() ?></h1>
			<?php if ( category_description() ) echo '<div class="archive-header-info">'.category_description().'</div>'; ?>
		</header>
		<?php include( 'modules/excerpt.php' ); ?>
	</div>
</div>
<?php get_sidebar(); get_footer(); ?>