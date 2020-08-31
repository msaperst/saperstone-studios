<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Contract {

    private $raw;
    private $id;
    private $link;
    private $type;
    private $name;
    private $address;
    private $number;
    private $email;
    private $date;
    private $location;
    private $session;
    private $details;
    private $amount = 0;
    private $deposit = 0;
    private $invoice;
    private $content;
    private $signature;
    private $initial;
    private $file;
    private $lineItems = array();

    function __construct() {
    }

    static function withId($id) {
        if (!isset ($id)) {
            throw new Exception("Contract id is required");
        } elseif ($id == "") {
            throw new Exception("Contract id can not be blank");
        }
        $contract = new Contract();
        $id = (int)$id;
        $sql = new Sql();
        $contract->raw = $sql->getRow("SELECT * FROM contracts WHERE id = $id;");
        if (!$contract->raw ['id']) {
            $sql->disconnect();
            throw new Exception("Contract id does not match any contracts");
        }
        $contract->id = $contract->raw['id'];
        $contract->link = $contract->raw['link'];
        $contract->type = $contract->raw['type'];
        $contract->name = $contract->raw['name'];
        $contract->address = $contract->raw['address'];
        $contract->number = $contract->raw['number'];
        $contract->email = $contract->raw['email'];
        $contract->date = $contract->raw['date'];
        $contract->location = $contract->raw['location'];
        $contract->session = $contract->raw['session'];
        $contract->details = $contract->raw['details'];
        $contract->amount = $contract->raw['amount'];
        $contract->deposit = $contract->raw['deposit'];
        $contract->invoice = $contract->raw['invoice'];
        $contract->content = $contract->raw['content'];
        $contract->signature = $contract->raw['signature'];
        $contract->initial = $contract->raw['initial'];
        $contract->file = $contract->raw['file'];
        $contract->raw['lineItems'] = $sql->getRows("SELECT * FROM contract_line_items WHERE contract = {$contract->id};");
        $sql->disconnect();
        foreach ($contract->raw['lineItems'] as $lineItem) {
            $contract->lineItems[] = new LineItem($lineItem['contract'], $lineItem['item'], $lineItem['amount'], $lineItem['unit']);
        }
        return $contract;
    }

    static function withParams($params) {
        $contract = new Contract();
        $sql = new Sql();
        //contract type
        if (!isset ($params['type'])) {
            $sql->disconnect();
            throw new Exception("Contract type is required");
        } elseif ($params['type'] == "") {
            $sql->disconnect();
            throw new Exception("Contract type can not be blank");
        } elseif (!in_array($params['type'], $sql->getEnumValues('contracts', 'type'))) {
            $sql->disconnect();
            throw new Exception ("Contract type is not valid");
        }
        $contract->type = $sql->escapeString($params ['type']);
        //contract name
        if (!isset ($params['name'])) {
            $sql->disconnect();
            throw new Exception("Contract name is required");
        } elseif ($params['name'] == "") {
            $sql->disconnect();
            throw new Exception("Contract name can not be blank");
        }
        $contract->name = $sql->escapeString($params ['name']);
        //contract session
        if (!isset ($params['session'])) {
            $sql->disconnect();
            throw new Exception("Contract session is required");
        } elseif ($params['session'] == "") {
            $sql->disconnect();
            throw new Exception("Contract session can not be blank");
        }
        $contract->session = $sql->escapeString($params ['session']);
        //contract content
        if (!isset ($params['content'])) {
            $sql->disconnect();
            throw new Exception("Contract content is required");
        } elseif ($params['content'] == "") {
            $sql->disconnect();
            throw new Exception("Contract content can not be blank");
        }
        $contract->content = $sql->escapeString($params ['content']);
        //optional values
        if (isset ($params ['amount']) && $params ['amount'] != "") {
            $contract->amount = floatval(str_replace('$', '', $params ['amount']));
        }
        if (isset ($params ['deposit']) && $params ['deposit'] != "") {
            $contract->deposit = floatval(str_replace('$', '', $params ['deposit']));
        }
        if (isset ($params ['address']) && $params ['address'] != "") {
            $contract->address = "'" . $sql->escapeString($params ['address']) . "'";
        }
        if (isset ($params ['number']) && $params ['number'] != "") {
            $contract->number = "'" . $sql->escapeString($params ['number']) . "'";
        }
        if (isset ($params ['email']) && $params ['email'] != "") {
            if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
                $sql->disconnect();
                throw new Exception("Contract email is not valid");
            }
            $contract->email = "'" . $sql->escapeString($params ['email']) . "'";
        }
        if (isset ($params ['date']) && $params ['date'] != "") {
            $date = $sql->escapeString($params ['date']);
            $format = 'Y-m-d';
            $d = DateTime::createFromFormat($format, $date);
            if (!($d && $d->format($format) === $date)) {
                $sql->disconnect();
                throw new Exception("Contract date is not the correct format");
            }
            $contract->date = "'" . $date . "'";
        }
        if (isset ($params ['location']) && $params ['location'] != "") {
            $contract->location = "'" . $sql->escapeString($params ['location']) . "'";
        }
        if (isset ($params ['details']) && $params ['details'] != "") {
            $contract->details = "'" . $sql->escapeString($params ['details']) . "'";
        }
        if (isset ($params ['invoice']) && $params ['invoice'] != "") {
            $contract->invoice = "'" . $sql->escapeString($params ['invoice']) . "'";
        }
        if (isset ($params ['lineItems']) && !empty($params ['lineItems'])) {
            foreach ($params ['lineItems'] as $lineItem) {
                $amount = floatval(str_replace('$', '', $lineItem ['amount']));
                $item = $unit = NULL;
                if (isset ($lineItem ['item']) && $lineItem ['item'] != "") {
                    $item = $sql->escapeString($lineItem ['item']);
                }
                if (isset ($lineItem ['unit']) && $lineItem ['unit'] != "") {
                    $unit = $sql->escapeString($lineItem ['unit']);
                }
                $contract->lineItems[] = new LineItem(NULL, $item, $amount, $unit);
            }
        }
        $sql->disconnect();
        return $contract;
    }

    function getId() {
        return $this->id;
    }

    function getType() {
        return $this->type;
    }

    function getName() {
        return $this->name;
    }

    function getEmail() {
        return $this->email;
    }

    function getDeposit() {
        return $this->deposit;
    }

    function getInvoice() {
        return $this->invoice;
    }

    /**
     * Only return basic information
     * id, type, name, session
     */
    function getDataBasic() {
        return array_diff_key($this->raw, ['link' => '', 'address' => '', 'number' => '', 'email' => '', 'date' => '', 'location' => '', 'details' => '', 'amount' => '', 'deposit' => '', 'invoice' => '', 'content' => '', 'signature' => '', 'initial' => '', 'file' => '', 'lineItems' => '']);
    }

    function getDataArray() {
        return $this->raw;
    }

    function create() {
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to create contract");
        }
        $sql = new Sql();
        $address = "NULL";
        if ($this->address != NULL) {
            $address = $this->address;
        }
        $number = "NULL";
        if ($this->number != NULL) {
            $number = $this->number;
        }
        $email = "NULL";
        if ($this->email != NULL) {
            $email = $this->email;
        }
        $date = "NULL";
        if ($this->date != NULL) {
            $date = $this->date;
        }
        $location = "NULL";
        if ($this->location != NULL) {
            $location = $this->location;
        }
        $details = "NULL";
        if ($this->details != NULL) {
            $details = $this->details;
        }
        $invoice = "NULL";
        if ($this->invoice != NULL) {
            $invoice = $this->invoice;
        }
        $lastId = $sql->executeStatement("INSERT INTO `contracts` (`link`, `type`, `name`, `address`, `number`, `email`, `date`, `location`,`session`, `details`, `amount`, `deposit`, `invoice`, `content`) VALUES ('','{$this->type}','{$this->name}',$address,$number,$email,$date,$location,'{$this->session}',$details, {$this->amount},{$this->deposit},$invoice,'{$this->content}');");
        $link = md5($lastId . $this->type . $this->name . $this->session);
        $sql->executeStatement("UPDATE `contracts` SET `link` = '$link' WHERE `id` = $lastId;");
        foreach ($this->lineItems as $lineItem) {
            /* @var $lineItem LineItem */
            $lineItem->setContract($lastId);
            $lineItem->create();
        }
        $sql->disconnect();
        $this->id = $lastId;
        $contract = self::withId($lastId);
        $this->link = $contract->link;
        $this->raw = $contract->getDataArray();
        return $lastId;
    }

    function sign($params) {
        $sql = new Sql();
        //contract name
        if (!isset ($params['name'])) {
            $sql->disconnect();
            throw new Exception("Contract contact name is required");
        } elseif ($params['name'] == "") {
            $sql->disconnect();
            throw new Exception("Contract contact name can not be blank");
        }
        $this->name = $sql->escapeString($params['name']);
        //contract address
        if (!isset ($params['address'])) {
            $sql->disconnect();
            throw new Exception("Contract contact address is required");
        } elseif ($params['address'] == "") {
            $sql->disconnect();
            throw new Exception("Contract contact address can not be blank");
        }
        $this->address = $sql->escapeString($params['address']);
        //contract number
        if (!isset ($params['number'])) {
            $sql->disconnect();
            throw new Exception("Contract contact number is required");
        } elseif ($params['number'] == "") {
            $sql->disconnect();
            throw new Exception("Contract contact number can not be blank");
        }
        $this->number = $sql->escapeString($params['number']);
        //contract email
        if (!isset ($params['email'])) {
            $sql->disconnect();
            throw new Exception("Contract contact email is required");
        } elseif ($params['email'] == "") {
            $sql->disconnect();
            throw new Exception("Contract contact email can not be blank");
        } elseif (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            $sql->disconnect();
            throw new Exception("Contract contact email is not valid");
        }
        $this->email = $sql->escapeString($params['email']);
        //contract signature
        if (!isset ($params['signature'])) {
            $sql->disconnect();
            throw new Exception("Contract signature is required");
        } elseif ($params['signature'] == "") {
            $sql->disconnect();
            throw new Exception("Contract signature can not be blank");
        }
        $this->signature = $sql->escapeString($params['signature']);
        //contract initial
        if (!isset ($params['initial'])) {
            $sql->disconnect();
            throw new Exception("Contract initials are required");
        } elseif ($params['initial'] == "") {
            $sql->disconnect();
            throw new Exception("Contract initials can not be blank");
        }
        $this->initial = $sql->escapeString($params['initial']);
        //contract content
        if (!isset ($params['content'])) {
            $sql->disconnect();
            throw new Exception("Contract content is required");
        } elseif ($params['content'] == "") {
            $sql->disconnect();
            throw new Exception("Contract content can not be blank");
        }
        $this->content = $sql->escapeString($params['content']);

        //set up our file
        $file = DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'contracts' . DIRECTORY_SEPARATOR . $this->name . ' - ' . date('Y-m-d') . ' - ' . ucfirst($this->type) . ' Contract.pdf';
        $sql->executeStatement("UPDATE `contracts` SET `name` = '{$this->name}', `address` = '{$this->address}', `number` = '{$this->number}',
        `email` = '{$this->email}', `signature` = '{$this->signature}', `initial` = '{$this->initial}', `content` = '{$this->content}', 
        `file` = '$file' WHERE `id` = {$this->id};");
        $sql->disconnect();

        // sanitize out content
        $content = str_replace("\\n", '', $this->content);
        $content = str_replace("\\\"", '"', $content);
        $content = str_replace("\\'", '\'', $content);

        // look at some formatting
        $customCSS = file_get_contents(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'mpdf.css');
        // setup our footer
        $footer = "<div align='left'><u>LAS</u>/<img src='{$this->initial}' style='height:20px; vertical-align:text-bottom;' /></div>";

        // create/save pdf
        require dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'autoload.php';
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->SetHTMLFooter($footer);
        $mpdf->WriteHTML($customCSS, 1);
        $mpdf->WriteHTML($content);
        $mpdf->Output(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . $file);

        return $file;
    }
}