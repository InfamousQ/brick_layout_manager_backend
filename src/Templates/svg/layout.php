<?php /** @var \InfamousQ\LManager\Models\Layout $layout */?>
<?php /** @var array $module_definitions */ ?>
<?php /** @var array $module_usages */ ?>
<!DOCTYPE svg>
<svg version="1.1" width="100%" height="100%" viewBox="0 0 <?= $layout->w ?> <?= $layout->h ?>" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
	<defs>
		<!-- Grid -->
		<symbol id="module-grid" viewBox="0 0 32 32">
			<rect x="0" y="0" width="32" height="32" fill="white" stroke="black" stroke-width="1" stroke-opacity="0.2" />
			<!-- <rect x="1" y="1" width="30" height="30" fill="white"></rect> -->
		</symbol>
		<pattern id="module-grid-pattern" x="0" y="0" width="1" height="1" patternUnits="userSpaceOnUse">
			<use x="0" y="0" width="1" height="1" xlink:href="#module-grid" />
		</pattern>
		<pattern id="brick-grid-pattern" width="1" height="1" patternUnits="userSpaceOnUse">
			<rect x="0" y="0" width="1" height="1" stroke="black" stroke-width="0.1" stroke-opacity="0.2" fill="none" />
		</pattern>

		<!-- Stud pattern -->
		<symbol id="stud">
			<circle cx="0.5" cy="0.5" r="0.3" stroke="black" stroke-width="0.1" stroke-opacity="0.1" fill="none" />
		</symbol>
		<pattern id="stud-pattern" width="1" height="1" patternUnits="userSpaceOnUse">
			<use x="0" y="0" width="1" height="1" xlink:href="#stud" />
		</pattern>
		<symbol id="stud-module" viewBox="0 0 32 32">
			<rect x="0" y="0" width="32" height="32" fill="url(#stud-pattern)" />
		</symbol>
		<pattern id="stud-module-pattern" width="1" height="1" patternUnits="userSpaceOnUse">
			<use x="0" y="0" width="1" height="1" xlink:href="#stud-module" />
		</pattern>

		<!-- Module definitions -->
<?php foreach ($module_definitions as $module_id => $module_definition) : ?>
		<symbol id="module-symbol-<?= $module_id ?>" viewBox="0 0 <?= $module_definition['w'] ?> <?= $module_definition['h'] ?>">
<?php foreach ($module_definition['plates'] as $plate) : ?>
			<rect x="<?= $plate['x'] ?>" y="<?= $plate['y'] ?>" height="<?= $plate['h'] ?>" width="<?= $plate['w'] ?>" fill="#<?= $plate['fill'] ?>" />
<?php endforeach; ?>
		</symbol>
<?php endforeach; ?>

		<!-- Module groups -->
<?php foreach (array_keys($module_definitions) as $module_id) : ?>
		<g id="module-group-<?= $module_id ?>">
			<use x="0" y="0" width="1" height="1" xlink:href="#module-symbol-<?= $module_id ?>" />
		</g>
<?php endforeach; ?>
	</defs>

	<!-- Base -->
	<rect x="0" y="0" width="<?= $layout->w?>" height="<?= $layout->h ?>" fill="url(#module-grid-pattern)"/>

	<!-- Module locations -->
<?php foreach($module_usages as $module_usage) : ?>
	<use x="<?= $module_usage['x'] ?>" y="<?= $module_usage['y'] ?>" xlink:href="#module-group-<?= $module_usage['module_id'] ?>" />
<?php endforeach; ?>

	<!-- Stud pattern -->
	<rect x="0" y="0" width="<?= $layout->w?>" height="<?= $layout->h ?>" fill="url(#stud-module-pattern)"/>
</svg>