<?php

class Youtube extends CI_Controller{
    
    public function index(){
        $this->load->view('youtube');
        //echo 'success';
     
    }
    public function get_videoid(){
        $this->load->model('youtube_m','youtube');
        $vid= $this->input->post('vid');
        if($this->youtube->save($vid)){
            return redirect(base_url());
        } else {
         echo 'try again';
        };
        
//    if ($this->db->insert(youtube, $vid)){
//        $message = "All's well";
//    }
//    else {
//        $message = "whoops!";
//    }
//    redirect('/Youtube/index/', 'refresh');

    
     
    }
    
}
//