<?php
return array(
    0         => $this->setResponseHeader(500) . $this->setContentType('xhtml'),
    'doctype' => $this->setDoctype(),
    'text'    => $this->convArrayToSting($this->getTplVar(), '</p><p>'),
);
?>
