<?php /** @var \InfamousQ\LManager\Models\Module $module */?>
<?php /** @var array $plate_rects */ ?>
<!DOCTYPE svg>
<svg version="1.1" width="100%" height="100%" viewBox="0 0 <?= $module->w ?> <?= $module->h ?>" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
	<defs>
		<pattern id="grid" width="1" height="1" patternUnits="userSpaceOnUse">
			<rect x="0" y="0" width="1" height="1" stroke="black" stroke-width="0.1" stroke-opacity="0.2" fill="none" />
		</pattern>
		<pattern id="stud" width="1" height="1" patternUnits="userSpaceOnUse">
			<circle cx="0.5" cy="0.5" r="0.3" stroke="black" stroke-width="0.1" stroke-opacity="0.1" fill="none" />
		</pattern>
	</defs>

	<rect fill="url(#grid)" x="0" y="0" width="100%" height="100%" />
	<g id="rect-container">
		<!-- Plates -->
<?php foreach($plate_rects as $plate) : ?>
		<rect x="<?= $plate['x'] ?>" y="<?= $plate['y'] ?>" width="<?= $plate['w'] ?>" height="<?= $plate['h'] ?>" fill="#<?= $plate['fill']?>" />
<?php endforeach; ?>
	</g>
	<rect fill="url(#stud)" x="0" y="0" width="100%" height="100%" />

</svg>