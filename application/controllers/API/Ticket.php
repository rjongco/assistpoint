<?php

class Ticket extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        parent::requireLogin();
        $this->load->model('core/Session_model', 'Session');
        $this->load->model('ticket/Ticket_model', 'Tickets');
        $this->load->model('user/User_model', 'Users');
    }

    public function create()
    { 
        $create = $this->Tickets->create($_POST);
        $this->sendJSON(array('result'=>$create));
    }

    public function getStatus(){
        $this->sendJSON($this->Tickets->getAllStatus());
    }

    public function getCategories(){
        $this->sendJSON($this->Tickets->getAllCategories());
    }

    public function getPriorities(){
        $this->sendJSON($this->Tickets->getAllPriorities());
    }

    public function getSeverities(){
        $this->sendJSON($this->Tickets->getAllSeverities());
    }

    public function upload_attachment()
    {

        if (isset($_POST) == true) {
            //generate unique file name
            date_default_timezone_set('Asia/Calcutta');
            $curr_date = date('dmY');
            $curr_time = date('His');
            $fileName = $curr_date . $curr_time . '-' . basename($_FILES["file"]["name"]);
            $fileName = str_replace(" ", "_", $fileName);

            //file upload path
            $targetDir = "uploads/";
            if (!is_dir($targetDir))
                mkdir($targetDir, 0777);
            $targetFilePath = $targetDir . $fileName;

            //allow certain file formats
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            $allowTypes = array('xlsx', 'png', 'jpeg', 'jpg', "zip", "rar", "docx", "doc", "xls", "csv");

            if (in_array($fileType, $allowTypes)) {
                //upload file to server
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                    //insert file data into the database if needed
                    $response['filename'] = $targetFilePath;
                    $response['original_file_name'] = $_FILES["file"]["name"];
                    $response['status'] = 'ok';
                } else {
                    $response['status'] = 'err';
                }
            } else {
                $response['status'] = 'type_err';
            }

            //render response data in JSON format
            echo json_encode($response);
        }
    }

    public function updateTicket(){
        $update = $this->Tickets->updateTicket($_POST['update_data'], $_POST['meta']);
        $thread_data = [
            'ticket' => $_POST['meta']['ticket_no'],
            'message' => $_POST['meta']['message'],
            'owner' => $this->Session->getLoggedDetails()['username'],
            'created' => time(),
            'type' => (int)$_POST['meta']['type']
        ];
        $meta = $this->Tickets->add_thread($thread_data);
        $this->sendJSON(array('result'=>$update));
    }

    public function addThreadMessage()
    {
        $thread_data = [
            'ticket' => $this->input->post('ticket_no'),
            'message' => $this->input->post('message'),
            'data' => json_encode($this->input->post('data')),
            'owner' => $this->Session->getLoggedDetails()['username'],
            'created' => time(),
            'type' => $this->input->post('type')
        ];
         if (trim($thread_data['message']) == '') {
            $this->sendJSON(array('result'=> -1));
        } else {
            $res = $this->Tickets->add_thread($thread_data);
            $this->Tickets->addAttachmentRef($this->input->post('data')['attachments'],  $this->input->post('ticket_no'));
            $this->sendJSON(array('result'=> $res));
        }
        
    }

    public function processIMAPEmail()
    {
        // if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
        //     throw new Exception('Request method must be POST!');
        // }
         
        // //Make sure that the content type of the POST request has been set to application/json
        // $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        // if(strcasecmp($contentType, 'application/json') != 0){
        //     throw new Exception('Content type must be: application/json');
        // }
         
        //Receive the RAW post data.
        $content = trim(file_get_contents("php://input"));

        // $content = "{\"html\":\"<div dir=\\\"ltr\\\">dsad<br clear=\\\"all\\\"><div><div dir=\\\"ltr\\\" class=\\\"gmail_signature\\\" data-smartmail=\\\"gmail_signature\\\"><div dir=\\\"ltr\\\"><div><div dir=\\\"ltr\\\"><div><div dir=\\\"ltr\\\"><div><div dir=\\\"ltr\\\"><div><div dir=\\\"ltr\\\"><div dir=\\\"ltr\\\"><div dir=\\\"ltr\\\"><span><span style=\\\"color:rgb(102,102,102);vertical-align:baseline;white-space:pre-wrap\\\"><div><span style=\\\"vertical-align:baseline\\\"><span><font face=\\\"verdana, sans-serif\\\"><p dir=\\\"ltr\\\" style=\\\"font-size:11pt;line-height:1.38;margin-top:0pt;margin-bottom:0pt\\\"><span style=\\\"font-size:10pt;color:rgb(0,0,0);background-color:transparent;vertical-align:baseline\\\">Regards,</span></p><p dir=\\\"ltr\\\" style=\\\"line-height:1.38;margin-top:0pt;margin-bottom:0pt\\\"><span style=\\\"color:rgb(0,0,0);background-color:transparent;vertical-align:baseline\\\">Madhurendra Sachan</span><span style=\\\"vertical-align:baseline\\\"> / Partner</span></p><div dir=\\\"ltr\\\" style=\\\"font-size:11pt;margin-left:0pt\\\"><table style=\\\"border:none;border-collapse:collapse\\\"><colgroup><col width=\\\"367\\\"><col width=\\\"227\\\"></colgroup><tbody><tr style=\\\"height:44.25pt\\\"><td style=\\\"border-right:1.5pt solid rgb(102,102,102);vertical-align:top;padding:5pt\\\"><p dir=\\\"ltr\\\" style=\\\"line-height:1.44;margin-top:0pt;margin-bottom:0pt\\\"><span style=\\\"font-size:10pt;color:rgb(34,34,34);font-weight:700;vertical-align:baseline\\\">P:</span><span style=\\\"font-size:10pt;color:rgb(34,34,34);vertical-align:baseline\\\"> +91 775 499 9998     </span><span style=\\\"font-size:10pt;color:rgb(34,34,34);font-weight:700;vertical-align:baseline\\\">E:</span><span style=\\\"font-size:10pt;color:rgb(34,34,34);vertical-align:baseline\\\"> </span><span style=\\\"font-size:10pt;color:rgb(17,85,204);vertical-align:baseline\\\"><a href=\\\"mailto:madhurendra@tikaj.com\\\" target=\\\"_blank\\\">madhurendra@tikaj.com</a></span></p><p dir=\\\"ltr\\\" style=\\\"line-height:1.44;margin-top:0pt;margin-bottom:0pt\\\"><span style=\\\"font-size:10pt;color:rgb(34,34,34);font-weight:700;vertical-align:baseline\\\">A:</span><span style=\\\"font-size:10pt;color:rgb(34,34,34);vertical-align:baseline\\\"> Unit 258, Tower B1, Spaze iTech Park, Sector 49, Gurgaon, Haryana, India - 122001</span></p></td><td style=\\\"border-left:1.5pt solid rgb(102,102,102);vertical-align:top;padding:5pt\\\"><p dir=\\\"ltr\\\" style=\\\"line-height:1.44;margin-top:0pt;margin-bottom:0pt\\\"><span style=\\\"font-size:11pt;color:rgb(34,34,34);vertical-align:baseline\\\"><img src=\\\"https://lh6.googleusercontent.com/tDS8toYr6aFGn4XMPy4qM1d6g782N5GJ6E6SEXMN2h-Mjy0kV2X5RSStojfzdMCUHpzQNfodFNh9P2TXD-QSFnmEyubDKDHcZwvBL_2zxnjaf2efniF2YU_MhuNCR3CTKrgKIEi9\\\" width=\\\"135\\\" height=\\\"51\\\" style=\\\"border:none\\\"></span></p></td></tr></tbody></table></div></font></span><span><span style=\\\"font-size:8pt;font-family:Verdana;vertical-align:baseline\\\"><div><span style=\\\"vertical-align:baseline\\\"><span><span style=\\\"font-size:8pt;font-family:Verdana;vertical-align:baseline\\\"><br></span></span></span></div></span></span><span><span style=\\\"font-size:9pt;font-family:Verdana;vertical-align:baseline\\\">The information contained in this electronic mail message and any attachments hereto may be legally privileged and confidential. The information is intended only for the recipient(s) named in this message. If you are not the intended recipient you are notified that any use, disclosure, copying, printing or distribution is prohibited. If you have received this in error please contact the sender and delete this message and any attachments from your computer system. We do not guarantee that this message or any attachment to it is secure or free from errors. Any opinion or other information in this e-mail or its attachments that does not relate to the business of TIKAJ  is personal to the sender and is not given or endorsed by TIKAJ Technologies Private Limited.</span></span><font face=\\\"Verdana\\\" style=\\\"font-size:11pt\\\"><br></font></span></div></span></span></div></div></div></div></div></div></div></div></div></div></div></div></div></div>\\n\",\"text\":\"dsad\\n\\nRegards,\\n\\nMadhurendra Sachan / Partner\\n\\nP: +91 775 499 9998     E: madhurendra@tikaj.com\\n\\nA: Unit 258, Tower B1, Spaze iTech Park, Sector 49, Gurgaon, Haryana, India\\n- 122001\\n\\n\\nThe information contained in this electronic mail message and any\\nattachments hereto may be legally privileged and confidential. The\\ninformation is intended only for the recipient(s) named in this message. If\\nyou are not the intended recipient you are notified that any use,\\ndisclosure, copying, printing or distribution is prohibited. If you have\\nreceived this in error please contact the sender and delete this message\\nand any attachments from your computer system. We do not guarantee that\\nthis message or any attachment to it is secure or free from errors. Any\\nopinion or other information in this e-mail or its attachments that does\\nnot relate to the business of TIKAJ  is personal to the sender and is not\\ngiven or endorsed by TIKAJ Technologies Private Limited.\\n\",\"headers\":{\"return-path\":\"<madhurendra@tikaj.com>\",\"delivered-to\":\"all@tbc.tik.co\",\"received\":[\"from localhost (localhost [127.0.0.1]) by na.tikaaz.com (Postfix) with ESMTP id 585C197A31 for <test@tbc.tik.co>; Wed, 18 Sep 2019 11:52:53 +0000 (UTC)\",\"from na.tikaaz.com ([127.0.0.1]) by localhost (localhost [127.0.0.1]) (amavisd-new, port 10024) with ESMTP id CQofPpqwDVMg for <test@tbc.tik.co>; Wed, 18 Sep 2019 11:52:50 +0000 (UTC)\",\"from mail-wr1-f45.google.com (mail-wr1-f45.google.com [209.85.221.45]) by na.tikaaz.com (Postfix) with ESMTPS id 879757F395 for <test@tbc.tik.co>; Wed, 18 Sep 2019 11:52:49 +0000 (UTC)\",\"by mail-wr1-f45.google.com with SMTP id i1so6591096wro.4 for <test@tbc.tik.co>; Wed, 18 Sep 2019 04:52:49 -0700 (PDT)\"],\"x-virus-scanned\":\"Debian amavisd-new at ip-10-0-0-77.ap-south-1.compute.internal\",\"dkim-signature\":\"v=1; a=rsa-sha256; c=relaxed/relaxed; d=tikaj-com.20150623.gappssmtp.com; s=20150623; h=mime-version:from:date:message-id:subject:to; bh=mjStQLn9nrjsAA1z6wkHgSCNG9Aqk2du/Ui1AZwRq5A=; b=ByFxnny5kDFQeqkQSoOx5XHfDt1Xr3Jf3W3iUex4G9So+dezUrWjxcupp36ZfZypA3 jbtqltEEQl+mqbWEwAGCYGAN1X0xokMuB7cN9CeQQ0b5D44mBaaTXwBaYdVFeb4/Ie+U wODXRkY1P6dj/Q4YWfppk2Hx2lTYE96esOXtbNnU8bSPBdizI6sex3WFJjQPMGeuNkux /tiNVenCYiN2AVXar7jDV9d5dTAt+DhoHvT8ScDDy+oPNpNFfY/LCep+J+/PkNNqlm5+ h0E8KqxI6/FFc7fb0Yfyw5TOf6smyaJdOJ6xM59WEHf7aQZyUpXu75QpUAMSD1AuBIpw ishQ==\",\"x-google-dkim-signature\":\"v=1; a=rsa-sha256; c=relaxed/relaxed; d=1e100.net; s=20161025; h=x-gm-message-state:mime-version:from:date:message-id:subject:to; bh=mjStQLn9nrjsAA1z6wkHgSCNG9Aqk2du/Ui1AZwRq5A=; b=dzY7381avu8junusUj8rh7TB1X5j+bcC0LWuSc8Je7Q6b9lxbkHl73mk+4BYp0a6KR orA7tpblzKVgN9BZcsASxWji8nSJ9OVgSuCkVWk8Ps5iXfX0p+iy1XlwUf0IltUFmwj3 hTZF5zdOTfS0IKSCoJ0kHoVV2GgusS3AC+kh3Pb3kwLKKThQxvspNmA765DSWI46by/a hGbTOCkwBLbPyswGRKPwWfphRAIfIICo60XGaDlxFseRGeW2zprNRIPEIlh3pmOGPKQF wm9FM/l287Bx4SUqauF2OvNncSwXQYtlT8Qd7bWtpYyIrbxThTRBcrzt1kXwsuDYii0t 2Rtw==\",\"x-gm-message-state\":\"APjAAAWjC36nvN7oi9K9xU9yP59UvVIZy/QJICxfiXo9RQ5AM4WHmn0C UKmoKUpWjtZpfb9gFbo2VHO14c09R6Uo0WVL+bRSOzJPJzQ=\",\"x-google-smtp-source\":\"APXvYqxy4teJ6NmVx4oQ7ah7UhMbdyoyFXSJ+gyWKUUZURgS8LX2HHna9FC9egO03daz7tirXL9e9kHuphWLwJdY2uA=\",\"x-received\":\"by 2002:adf:f081:: with SMTP id n1mr2719208wro.273.1568807566220; Wed, 18 Sep 2019 04:52:46 -0700 (PDT)\",\"mime-version\":\"1.0\",\"from\":\"Madhurendra Sachan <madhurendra@tikaj.com>\",\"date\":\"Wed, 18 Sep 2019 17:22:09 +0530\",\"message-id\":\"<CAMxLWuXOMZ8Wd+3GYU7aa6A3Dnbo-1-7WNHoSdyCTx320PuRbg@mail.gmail.com>\",\"subject\":\"[#TIK1568791543] Facing Issue in logging in to portal\",\"to\":\"test@tbc.tik.co\",\"content-type\":\"multipart/alternative; boundary=\\\"000000000000b4aef10592d279ce\\\"\"},\"subject\":\"This is a Ticket. Please raise it.\",\"messageId\":\"CAMxLWuXOMZ8Wd+3GYU7aa6A3Dnbo-1-7WNHoSdyCTx320PuRbg@mail.gmail.com\",\"priority\":\"normal\",\"from\":[{\"address\":\"manager.one@domain.com\",\"name\":\"Madhurendra Sachan\"}],\"to\":[{\"address\":\"test@tbc.tik.co\",\"name\":\"\"}],\"date\":\"2019-09-18T11:52:09.000Z\",\"receivedDate\":\"2019-09-18T11:52:53.000Z\",\"_id\":\"CAMxLWuXOMZ8Wd+3GYU7aa6A3Dnbo-1-7WNHoSdyCTx320PuRbg@mail.gmail.com\",\"attributes\":{},\"receivedBy\":[[\"test@tbc.tik.co\"]],\"receivedFrom\":[[\"test@tbc.tik.co\"],[\"test@tbc.tik.co\"],[\"test@tbc.tik.co\"]]}";
         
        //Attempt to decode the incoming RAW post data from JSON.
        $decoded = json_decode($content, true);
         
        //If json_decode failed, the JSON is invalid.
        if(!is_array($decoded)){
            throw new Exception('Received content contained invalid JSON!');
        }

        var_dump($decoded);
        $this->Tickets->processIMAPEmail($decoded);

    }

    public function sendEmail(){
        $this->load->model("notification/Email_model", "Email");
        print_r($this->Email->send('rj.inovino@gmail.com', 'rj.inovino@gmail.com', 'Hello', 'Hello', 'rj.inovino@gmail.com'));
    }

    


// End of Class
}