<?php
    session_start();
    //$_SESSION['postdata']=json_decode($_POST);
    $_SESSION['username'] = $_POST['username'];

    include "../config.inc";

    $conn=mysqli_connect("localhost",$db_user,$db_password,$db_name,$db_port);

    if(mysqli_connect_errno())
    {
        echo "connect db failed:".mysqli_connect_error();
    }
    else
    {
        header('Content-Type: application/json');
        mysqli_set_charset($conn,"utf8");
        $username=$_SESSION['username'];
        $sql_select="SELECT id,name FROM tb_user WHERE name='$username'";
        $ret=mysqli_query($conn,$sql_select);
        $cnt=mysqli_num_rows($ret);
        $user_id=0;
        if($cnt<1)
        {
            $sql_select="insert into tb_user(name) values('$username')";
            mysqli_query($conn,$sql_select);
            $sql_select="SELECT id,name FROM tb_user WHERE name='$username'";
            $ret=mysqli_query($conn,$sql_select);
        }
        $row=mysqli_fetch_row($ret);
        $_SESSION['user_id']=$row[0];
        $user_id=$row[0];

        // print( $row[0]);
    
        $sql_select="SELECT id,name, user_cnt, user_id0,user_id1 from tb_room WHERE user_id0=$user_id or user_id1=$user_id order by user_cnt DESC";
        $ret=mysqli_query($conn,$sql_select);
        $cnt=mysqli_num_rows($ret);
        if($cnt>=1)
        {
            $row=mysqli_fetch_row($ret);
            // we have a match
            if($row[2]>1)
            {
                // we are good
                class RetClass {
                    public $ret_code = 0;
                    public $username="";
                    public $rival="";
                    public $user_id=0;
                    public $rival_id=0;
                    public $room="";
                    public $first_user_id=0;
                };
                $jret=new RetClass();
                $jret->ret_code=0;
                $jret->username=$username;
                $jret->user_id=$user_id;
                $jret->room=$row[1];
                $jret->first_user_id=$row[3];
                if($user_id==$row[3])
                {
                    $jret->rival_id=$row[4];                    
                }
                else
                {
                    $jret->rival_id=$row[3];                    
                }                
                $sql_select="SELECT name from tb_user WHERE id=".$jret->rival_id;
                $ret=mysqli_query($conn,$sql_select);
                $row=mysqli_fetch_row($ret);                
                $jret->rival=$row[0];
                echo json_encode($jret,JSON_UNESCAPED_UNICODE);
            }
            else
            {
                if($row[3]==$user_id)
                {
                    // just myself
                    class RetClass {
                        public $ret_code=0;
                        public $username="";
                        public $rival="";
                        public $user_id=0;
                        public $rival_id=0;
                        public $room="";
                        public $first_user_id=0;
                    }
                    $jret=new RetClass();
                    $jret->ret_code=1;
                    $jret->room=$row[1];
                    $jret->username=$username;
                    $jret->user_id=$user_id;
                    echo json_encode($jret,JSON_UNESCAPED_UNICODE);
                }
                else
                {
                    // we got rival
                    class RetClass {
                        public $ret_code=0;
                        public $username="";
                        public $rival="";
                        public $user_id=0;
                        public $rival_id=0;
                        public $room="";
                        public $first_user_id=0;
                    }
                    $jret=new RetClass();
                    $jret->ret_code=0;
                    $jret->username=$username;
                    $jret->user_id=$user_id;
                    $jret->room=$row[1];
                    $jret->rival_id=$row[3];
                    $jret->first_user_id=$row[3];
                    $sql_select="SELECT name from tb_user WHERE id=".$jret->rival_id;
                    $ret=mysqli_query($conn,$sql_select);
                    $row=mysql_fetch_row($ret);
                    $jret->rival=$row[1];
                    // update and add myself to the room
                    $sql_select="UPDATE tb_room SET user_cnt=2, user_id1=$user_id WHERE id=".$row[0];
                    $ret=mysqli_query($conn,$sql_select);
                    // output
                    echo json_encode($jret,JSON_UNESCAPED_UNICODE);
                }
            }
        }
        else
        {
            // try to get into a new room
            $sql_select="SELECT id,name,user_cnt FROM tb_room WHERE user_cnt<=1 order by user_cnt desc";
            $ret=mysqli_query($conn,$sql_select);
            $cnt=mysqli_num_rows($ret);
            if($cnt<=0)
            {
                // no enough room, wait until next query
                class RetClass {
                    public $ret_code=0;
                    public $username="";
                    public $rival="";
                    public $user_id=0;
                    public $rival_id=0;
                    public $room="";
                    public $first_user_id=0;
                }
                $jret=new RetClass();
                $jret->ret_code=2;
                $jret->username=$username;
                $jret->user_id=$user_id;
                echo json_encode($jret,JSON_UNESCAPED_UNICODE);
            }
            else
            {
                $row=mysqli_fetch_row($ret);
                $_SESSION['room']=$row[1];
                $_SESSION['room_id']=$row[0];
                if($row[2]<1)
                {
                    $sql_update="UPDATE tb_room SET user_cnt = 1, user_id0=$user_id WHERE id=".$row[0];
                    $ret=mysqli_query($conn,$sql_update);
                    class RetClass {
                        public $ret_code=0;
                        public $username="";
                        public $rival="";
                        public $user_id=0;
                        public $rival_id=0;
                        public $room="";
                        public $first_user_id=0;
                    }
                    $jret=new RetClass();
                    $jret->ret_code=1;
                    $jret->room=$_SESSION['room'];
                    $jret->username=$username;
                    $jret->user_id=$user_id;
                    echo json_encode($jret,JSON_UNESCAPED_UNICODE);
                }
                else
                {                    
                    class RetClass {
                        public $ret_code=0;
                        public $username="";
                        public $rival="";
                        public $user_id=0;
                        public $rival_id=0;
                        public $room="";
                        public $first_user_id=0;
                    }
                    $jret=new RetClass();
                    $jret->ret_code=0;
                    $jret->username=$username;
                    $jret->user_id=$user_id;
                    $jret->room=$row[1];
                    $jret->rival_id=$row[3];
                    $jret->first_user_id=$row[3];
                    // update and add myself to the room
                    $sql_select="UPDATE tb_room SET user_cnt=2, user_id1=$user_id WHERE id=".$row[0];
                    $ret=mysqli_query($conn,$sql_select);
                    $sql_select="SELECT name from tb_user WHERE id=".$jret->rival_id;
                    $ret=mysqli_query($conn,$sql_select);
                    $row=mysql_fetch_row($ret);
                    $jret->rival=$row[1];
                    // output
                    echo json_encode($jret,JSON_UNESCAPED_UNICODE);
                }
            }
        }    
    }
    
    mysqli_close($conn);
?>