<?php 
  require_once '../vendor/phpoffice/phpword/Classes/PHPWord.php';
  // New class to replace PHPWord_Template in order to include cloneRow function (found @ https://jeroen.is/phpword-templates-with-repeating-rows/)
  class MyPHPWord_Template
  {
    /**
     * ZipArchive
     *
     * @var ZipArchive
     */
    private $_objZip;

    /**
     * Temporary Filename
     *
     * @var string
     */
    private $_tempFileName;

    /**
     * Document XML
     *
     * @var string
     */
    private $_documentXML;


    /**
     * Create a new Template Object
     *
     * @param string $strFilename
     */
    public function __construct($strFilename)
    {
        $this->_tempFileName = tempnam(sys_get_temp_dir(), '');
        if ($this->_tempFileName !== false) {
            // Copy the source File to the temp File
            if (!copy($strFilename, $this->_tempFileName)) {
                throw new PHPWord_Exception('Could not copy the template from ' . $strFilename . ' to ' . $this->_tempFileName . '.');
            }

            $this->_objZip = new ZipArchive();
            $this->_objZip->open($this->_tempFileName);

            $this->_documentXML = $this->_objZip->getFromName('word/document.xml');
        } else {
            throw new PHPWord_Exception('Could not create temporary file with unique name in the default temporary directory.');
        }
    }

    /**
     * Set a Template value
     *
     * @param mixed $search
     * @param mixed $replace
     */
    public function setValue($search, $replace)
    {
        $pattern = '|\$\{([^\}]+)\}|U';
        preg_match_all($pattern, $this->_documentXML, $matches);
        foreach ($matches[0] as $value) {
            $valueCleaned = preg_replace('/<[^>]+>/', '', $value);
            $valueCleaned = preg_replace('/<\/[^>]+>/', '', $valueCleaned);
            $this->_documentXML = str_replace($value, $valueCleaned, $this->_documentXML);
        }

        if (substr($search, 0, 2) !== '${' && substr($search, -1) !== '}') {
            $search = '${' . $search . '}';
        }

        if (!is_array($replace)) {
            if (!PHPWord_Shared_String::IsUTF8($replace)) {
                $replace = utf8_encode($replace);
            }
        }

        $this->_documentXML = str_replace($search, $replace, $this->_documentXML);
    }

    /**
     * Returns array of all variables in template
     */
    public function getVariables()
    {
        preg_match_all('/\$\{(.*?)}/i', $this->_documentXML, $matches);
        return $matches[1];
    }

    /**
     * Save Template
     *
     * @param string $strFilename
     */
    public function save($strFilename)
    {
        if (file_exists($strFilename)) {
            unlink($strFilename);
        }

        $this->_objZip->addFromString('word/document.xml', $this->_documentXML);

        // Close zip file
        if ($this->_objZip->close() === false) {
            throw new Exception('Could not close zip file.');
        }

        rename($this->_tempFileName, $strFilename);
    }
    /**
     * Clone a table row
     * 
     * @param mixed $search
     * @param mixed $numberOfClones
     */
	  public function cloneRow($search, $numberOfClones) {
      if(substr($search, 0, 2) !== '${' && substr($search, -1) !== '}') {
          $search = '${'.$search.'}';
      }
          
      $tagPos 	 = strpos($this->_documentXML, $search);
      $rowStartPos = strrpos($this->_documentXML, "<w:tr ", ((strlen($this->_documentXML) - $tagPos) * -1));
      $rowEndPos   = strpos($this->_documentXML, "</w:tr>", $tagPos) + 7;

      $result = substr($this->_documentXML, 0, $rowStartPos);
      $xmlRow = substr($this->_documentXML, $rowStartPos, ($rowEndPos - $rowStartPos));
      for ($i = 1; $i <= $numberOfClones; $i++) {
        $result .= preg_replace('/\$\{(.*?)\}/','\${\\1#'.$i.'}', $xmlRow);
      }
      $result .= substr($this->_documentXML, $rowEndPos);

      $this->_documentXML = $result;
    }
  }
  // MyPHPWord class, created to include MyPHPWord_Template class which features cloneRow function
  class MyPHPWord extends PHPWord {
    public function loadTemplate($strFilename)
    {
        if (file_exists($strFilename)) {
            $template = new MyPHPWord_Template($strFilename);
            return $template;
        } else {
            trigger_error('Template file ' . $strFilename . ' not found.', E_USER_ERROR);
        }
    }
  }
?>