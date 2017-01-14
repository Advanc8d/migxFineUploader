<?php

$formTpl .= '<div style="display:none;">';
$formTpl .= $this->_displaySimple($name, 'TextAreaTpl', $this->textMaxlength);
$formTpl .= '</div>';
$formTpl .= '[[!migxFineUploader? &tvname=`'.$name.'` ]]';