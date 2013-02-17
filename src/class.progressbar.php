<?php
# class ProgressBar
# version 1.00 
# by Mika Turin (turin@inbox.lv)
# any comments ang bugs found are welcome
# works ONLY under IE4 & more
# don't forget to use set_time_limit() on long processes

class ProgressBar
{
  var $left = 0;
  var $top = 0;
  var $width;
  var $height;
  var $min = 0;
  var $max = 100;
  var $step = 1;
  var $color     = '#0A246A';     # progress bar color
  var $bgr_color = '#FFFFFF';     # background color
  var $txt_color = '#FF0000';     # text color
  var $brd_color = '#000000';     # border color

  var $_code;
  var $_val = 0;

  function ProgressBar ($width, $height) # constructor
  {
    $this->width = $width;
    $this->height = $height;
    $this->_code = md5 (uniqid (''));
  }

  function Destroy ()
  {

  }

  function _calculatePercent ()
  {
    $p = round (($this->_val - $this->min) / ($this->max - $this->min) * 100);
    if ($p > 100) {$p = 100;}
    return $p;
  }

  function _calculateWidth ()
  {
    $w = round (($this->_val - $this->min) * ($this->width - 2) / ($this->max - $this->min));
    if ($this->_val <= $this->min) {$w = 0;}
    if ($this->_val >= $this->max) {$w = $this->width - 2;}
    return $w;
  }

  function setVal ($val)
  {
    if ($val > $this->max) {$val = $this->max;}
    if ($val < $this->min) {$val = $this->min;}
    $this->_val = $val;
  }

  function moveIt ($val)
  {
    $this->setVal ($val);
    $prc = $this->_calculatePercent ();
    $cw = $this->_calculateWidth ();
    echo '<script language="javascript">document.getElementByID(\'ptxt'.$this->_code.'\').innerText="'.$prc.'%";</script>'."\n";
    echo '<script language="javascript">document.getElementByID(\'pbar'.$this->_code.'\').style.width='.$cw.';</script>'."\n";
    flush ();
  }

  function getHtml ()
  {
  	$this->setVal ($this->_val);
    $prc = $this->_calculatePercent ();
    $cw = $this->_calculateWidth ();

  	$hh = $this->height;
  	if ($hh <= 100) {$koef = 0.0017 * $hh + 0.64;} else {$koef = 0.81;}
  	$px = round ($hh * $koef);

  	$size1 = 'width:'.$this->width.'px;height:'.$this->height.'px;';
  	$size2 = 'width:'.($this->width - 2).'px;height:'.($this->height - 2).'px;';
  	$position1 = 'position:absolute;top:'.$this->top.';left:'.$this->left.';';
  	$position2 = 'position:absolute;top:'.($this->top + 1).';left:'.($this->left + 1).';';
    $font = 'font-family:Tahoma;font-weight:bold;font-size:'.$px.'px;';

    $style1 = $position1.$size1.$font.'border:1px solid '.$this->brd_color.';text-align:center;background-color:'.$this->bgr_color.';';
    $style2 = $position2.$size2.$font.'color: '.$this->txt_color.';z-index:1;text-align:center;';
    $style3 = $position2.$font.'width:'.$cw.'px; height: '.($this->height-2).'px; background-color: '.$this->color.';z-index:0;';

    return
    '<div id="pbrd'.$this->_code.'" style="'.$style1.'"></div>'.
    '<div id="ptxt'.$this->_code.'" style="'.$style2.'">'.$prc.'%</div>'.
    '<div id="pbar'.$this->_code.'" style="'.$style3.'"></div>';
  }

  function drawHtml ()
  {
    echo $this->getHtml ();
  }
}
?>
