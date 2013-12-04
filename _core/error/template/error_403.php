<?php
return array(
    0      => $this->setResponseHeader(403) . $this->setContentType('html'),
    'doctype' => $this->setDoctype(),
    //'text'    => $this->convArrayToSting($this->getTplVar(), '</p><p>'),
);
?>