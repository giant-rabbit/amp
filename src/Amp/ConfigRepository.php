<?php
namespace Amp;
use Symfony\Component\Yaml\Yaml;

class ConfigRepository {

  /**
   * @param mixed $data
   */
  var $data;

  /**
   * @var array
   */
  var $descriptions;

  /**
   * @var array
   */
  var $examples;

  /**
   * @var string
   */
  var $file;

  public function __construct($file) {
    $this->file = $file;

    // FIXME externalize
    $this->descriptions = array(
      'httpd_type' => 'Type of webserver',
      'apache_dir' => 'Directory which stores Apache config files',
      'apache_tpl' => 'Apache configuration template',
      'nginx_dir' => 'Directory which stores nginx config files',
      'nginx_tpl' => 'Nginx configuration template',
      'mysql_type' => 'How to connect to MySQL admin (cli, dsn, linuxRamDisk)',
      'mysql_dsn' => 'Administrative credentials for MySQL',
    );

    // FIXME externalize
    $this->examples = array(
      'mysql_dsn' => 'mysql://user:pass@hostname'
    );
  }

  protected function load() {
    if ($this->data === NULL) {
      if (file_exists($this->getFile())) {
        $this->data = Yaml::parse(file_get_contents($this->getFile()));
      }
      else {
        $this->data = array(
          'parameters' => array(),
          'services' => array(),
        );
      }
    }
  }

  public function getParameter($key) {
    $this->load();
    return isset($this->data['parameters'][$key]) ? $this->data['parameters'][$key] : NULL;
  }

  public function setParameter($key, $value) {
    $this->load();
    $this->data['parameters'][$key] = $value;
  }

  public function unsetParameter($key) {
    $this->load();
    unset($this->data['parameters'][$key]);
  }

  public function save() {
    $this->load();
    file_put_contents($this->getFile(), Yaml::dump($this->data));
  }

  /**
   * @param string $file
   */
  public function setFile($file) {
    $this->file = $file;
  }

  /**
   * @return string
   */
  public function getFile() {
    return $this->file;
  }

  /**
   * @return array of parameter names (strings)
   */
  public function getParameters() {
    return array_keys($this->descriptions);
  }

  /**
   * @return string|NULL
   */
  public function getDescription($parameter) {
    return isset($this->descriptions[$parameter]) ? $this->descriptions[$parameter] : NULL;
  }

  /**
   * @return string|NULL
   */
  public function getExample($parameter) {
    return isset($this->examples[$parameter]) ? $this->examples[$parameter] : NULL;
  }

}