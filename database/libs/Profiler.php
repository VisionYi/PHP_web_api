<?php
namespace database\libs;

class Profiler {

    private $active = false;
    private $contents = array();

    public function addContent(
        $time,
        $fun_name,
        $statement = null,
        array $bind_data = null,
        $error = null
    ) {
        if (! $this->isActive()) {
            return;
        }

        $e = new \Exception;
        $this->contents[] = array(
            'duration'    => number_format($time, 5),
            'function'    => $fun_name,
            'statement'   => $statement,
            'bind_data'   => $bind_data,
            'error'       => $error,
            'trace'       => $e->getTraceAsString(),
        );
    }

    public function getContents() {
        return $this->contents;
    }

    public function setActive(bool $active) {
        $this->active = $active;
    }

    public function isActive(){
        return $this->active;
    }

    public function resetContents() {
        $this->contents = array();
    }
}
 ?>
