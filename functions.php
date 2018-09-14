<?php
/*
 * Takes a string and echoes it safe from XSS.
 * Intended use is to run data that comes from user-input through it(i.e. from database).
 */
function o($string){
	echo htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}