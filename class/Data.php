<?php

include("Node.php");

class Data
{

    private $data;
    private $dataRaw;
    private $url;
    private $nodes;

    /**
     * Data constructor.
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->catchData();
    }


    private function catchData(){
        $this->dataRaw = file_get_contents($this->url);
        $this->data = json_decode($this->dataRaw,true);
        $this->parseData();
    }

    private function parseData(){
        $this->nodes = Array();
        foreach($this->data['nodes'] as $nodeData){

            ////////
            $flags = new NodeFlags($nodeData['flags']['gateway'],$nodeData['flags']['online']);
            ////////

            ////////
            if(isset($nodeData['statistics']['traffic'])){
                $mgmtTx = new Traffic($nodeData['statistics']['traffic']['mgmt_tx']['packets'],$nodeData['statistics']['traffic']['mgmt_tx']['bytes']);
                $forward = new Traffic($nodeData['statistics']['traffic']['forward']['packets'],$nodeData['statistics']['traffic']['forward']['bytes']);
                $rx = new Traffic($nodeData['statistics']['traffic']['rx']['packets'],$nodeData['statistics']['traffic']['rx']['bytes']);
                $mgmtRx = new Traffic($nodeData['statistics']['traffic']['mgmt_rx']['packets'],$nodeData['statistics']['traffic']['mgmt_rx']['bytes']);
                $tx = new Traffic($nodeData['statistics']['traffic']['tx']['packets'],$nodeData['statistics']['traffic']['tx']['bytes']);
                $nodeTraffic = new NodeTraffic($mgmtTx,$forward,$rx,$mgmtRx,$tx);
            }
            else{
                $nodeTraffic = new NodeTraffic(new Traffic(0,0),new Traffic(0,0),new Traffic(0,0),new Traffic(0,0),new Traffic(0,0));
            }
            ////////

            ////////
            if(isset($nodeData['statistics']['memory_usage']))
                $memoryUsage = $nodeData['statistics']['memory_usage'];
            else
                $memoryUsage = 0;
            if(isset($nodeData['statistics']['clients']))
                $clients = $nodeData['statistics']['clients'];
            else
                $clients = 0;
            if(isset($nodeData['statistics']['rootfs_usage']))
                $rootfsUsage = $nodeData['statistics']['rootfs_usage'];
            else
                $rootfsUsage = 0;
            if(isset($nodeData['statistics']['uptime']))
                $uptime = $nodeData['statistics']['uptime'];
            else
                $uptime = 0;
            if(isset($nodeData['statistics']['gateway']))
                $gateway = $nodeData['statistics']['gateway'];
            else
                $gateway = 0;
            if(isset($nodeData['statistics']['loadavg']))
                $loadavg = $nodeData['statistics']['loadavg'];
            else
                $loadavg = 0;

            $statistics = new NodeStatistics($memoryUsage
                                            ,$clients
                                            ,$rootfsUsage
                                            ,$uptime
                                            ,$gateway
                                            ,$loadavg
                                            ,$nodeTraffic);
            ////////


            ////////
            $hostname = $nodeData['nodeinfo']['hostname'];

            if(isset($nodeData['nodeinfo']['hardware']['nproc']))
                $nproc = $nodeData['nodeinfo']['hardware']['nproc'];
            else
                $nproc = 0;

            $hardware = new NodeHardware($nproc,$nodeData['nodeinfo']['hardware']['model']);

            if(isset($nodeData['nodeinfo']['location']))
                $location = new NodeLocation($nodeData['nodeinfo']['location']['latitude'],$nodeData['nodeinfo']['location']['longitude']);
            else
                $location = new NodeLocation(0,0);
            if(isset($nodeData['nodeinfo']['system']))
                $system = new NodeSystem($nodeData['nodeinfo']['system']['site_code']);
            else
                $system = new NodeSystem("");

            $autoupdate = new NodeAutoupdater($nodeData['nodeinfo']['software']['autoupdater']['branch'],$nodeData['nodeinfo']['software']['autoupdater']['enabled']);
            $fastd = new NodeFastd($nodeData['nodeinfo']['software']['fastd']['version'],$nodeData['nodeinfo']['software']['fastd']['enabled']);
            if(isset($nodeData['nodeinfo']['software']['batman-adv']['compat']))
                $compat = $nodeData['nodeinfo']['software']['batman-adv']['compat'];
            else
                $compat = "";
            $batman = new NodeBadtmanAdv($nodeData['nodeinfo']['software']['batman-adv']['version'],$compat);
            $firmware = new NodeFirmware($nodeData['nodeinfo']['software']['firmware']['base'],$nodeData['nodeinfo']['software']['firmware']['release']);
            $software = new NodeSoftware($autoupdate,$fastd,$batman,$firmware);

            $node_id = $nodeData['nodeinfo']['node_id'];

            if(isset($nodeData['nodeinfo']['owner']['contact']))
                $owner = new NodeOwner($nodeData['nodeinfo']['owner']['contact']);
            else
                $owner = new NodeOwner("");

            if(isset($nodeData['nodeinfo']['network']['mesh']))
                $mesh = $nodeData['nodeinfo']['network']['mesh'];
            else
                $mesh = "";

            $network = new NodeNetwork($nodeData['nodeinfo']['network']['addresses'],$nodeData['nodeinfo']['network']['mesh_interfaces'],$nodeData['nodeinfo']['network']['mac'],$mesh);

            $nodeinfo = new NodeInfo($hostname,$hardware,$location,$system,$software,$node_id,$owner,$network);

            ////////

            ////////
            $node = new Node($nodeData['firstseen'],$nodeData['lastseen'],$flags,$statistics,$nodeinfo);

            $this->nodes[$node->getNodeinfo()->getNetwork()->getMac()] = $node;

            ////////



            //echo $node->getNodeinfo()->getNetwork()->getMac();
            //echo "<br>####################################<br>";
        }
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getDataRaw()
    {
        return $this->dataRaw;
    }

    /**
     * @return mixed
     */
    public function getNodes()
    {
        return $this->nodes;
    }





}