<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use \Firebase\JWT\JWT;

return function (App $app) {
    $container = $app->getContainer();

    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });
    $app->add(function ($req, $res, $next) {
        $response = $next($req, $res);
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, token')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    });

    // ini untuk login
    $app->post('/login/', function (Request $request, Response $response, array $args) {
        $input = $request->getParsedBody();
        $username=trim(strip_tags($input['username']));
        $password=trim(strip_tags($input['password']));
        $sql = "SELECT id_user, username  FROM `user` WHERE username=:username AND `password`=:password";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("username", $username);
        $sth->bindParam("password", $password);
        $sth->execute();
        $user = $sth->fetchObject();       
        if(!$user) {
            return $this->response->withJson(['status' => 'error', 'message' => 'These credentials do not match our records username.'],200);  
        }
        $settings = $this->get('settings');       
        $token = array(
            'id_user' =>  $user->id_user, 
            'username' => $user->username
        );
        $token = JWT::encode($token, $settings['jwt']['secret'], "HS256");
        return $this->response->withJson(['status' => 'success','data'=>$user, 'token' => $token],200); 
    });

    // ini untuk register
    $app->post('/register', function (Request $request, Response $response, array $args) {
        $input = $request->getParsedBody();
        $username=trim(strip_tags($input['username']));
        $email=trim(strip_tags($input['email']));
        // $api_key=trim(strip_tags($input['api_key']));
        $password=trim(strip_tags($input['password']));
        $sql = "INSERT INTO user(username, api_key,  password) 
                VALUES(:username, :api_key,  :password)";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("username", $username);;
        $sth->bindParam("email", $email);;
        // $sth->bindParam("api_key", $api_key);
        $sth->bindParam("password", $password); 
        $StatusInsert=$sth->execute();
        if($StatusInsert){
            $id_user=$this->db->lastInsertId();     
            $settings = $this->get('settings'); 
            $token = array(
                'id_user' =>  $id_user, 
                'username' => $username
            );
            $token = JWT::encode($token, $settings['jwt']['secret'], "HS256");
            $dataUser=array(
                'id$id_user'=> $id_user,
                'username'=> $username
                );
            return $this->response->withJson(['status' => 'success','data'=>$dataUser, 'token'=>$token],200); 
        } else {
            return $this->response->withJson(['status' => 'error','data'=>'error insert user.'],200); 
        }
    });

    //apikey
    $app->group('/api', function(\Slim\App $app) {
        $cekAPIKey = function($request, $response, $next){
            $key = $request->getQueryParam("key");
    
            if(!isset($key)){
                return $response->withJson(["status" => "API Key required"], 401);
            }
            
            $sql = "SELECT * FROM user WHERE api_key=:api_key";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":api_key" => $key]);
            
            if($stmt->rowCount() > 0){
                $result = $stmt->fetch();
                if($key == $result["api_key"]){
                    if($result["hit"]>1000){
                        return $response->withJson(["status" => "Request Has Reached Limit"], 401);
                    }else{
                        $sql = "UPDATE user SET hit=hit+1 WHERE api_key=:api_key";
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([":api_key" => $key]);
                        
                        return $response = $next($request, $response);
                    } 
                   
                }
            }
            return $response->withJson(["status" => "Unauthorized"], 401);
        
        };
        $app->get("/user/{id_user}", function (Request $request, Response $response, array $args){
            $id_user = trim(strip_tags($args["id_user"]));
            $sql = "SELECT id_user, username FROM `user` WHERE id_user=:id_user";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("id_user", $id_user);
            $stmt->execute();
            $mainCount=$stmt->rowCount();
            $result = $stmt->fetchObject();
            if($mainCount==0) {
                return $this->response->withJson(['status' => 'error', 'message' => 'no result data.'],200); 
            }
            return $response->withJson(["status" => "success", "data" => $result], 200);
        })->add($cekAPIKey);

    
//kota
        $app->get("/kota/", function (Request $request, Response $response){
            
            $sql = "SELECT * FROM kota";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);

        })->add($cekAPIKey);

        $app->get("/kota/{id}", function (Request $request, Response $response, 
            $args){
            $id = $args["id"];
            $sql = "SELECT * FROM kota WHERE id_kota=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $mainCount=$stmt->rowCount();
            $result = $stmt->fetch();
            if($mainCount==0) {
                return $this->response->withJson(['status' => 'error', 'message' => 'kota Tidak Ditemukan.'],200); 
            }
            return $response->withJson(["status" => "success", "data" => $result], 200);
                
        })->add($cekAPIKey);

        $app->get("/kota/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT * FROM kota WHERE namaKota LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if($result==null) {
                return $this->response->withJson(['status' => 'error', 'message' => 'Kota Tidak Ditemukan.'],200); 
            }
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        })->add($cekAPIKey);

        $app->post("/kota/", function (Request $request, Response $response){
            $new_kota = $request->getParsedBody();
            $sql = "INSERT INTO kota (namaKota) VALUE (:namaKota)";
            $stmt = $this->db->prepare($sql);
            $_data_1 = $new_kota['namaKota'];
            $rsp = array();
            if(!empty($_data_1)){
                $data = [
                ":namaKota" => $new_kota["namaKota"]
                ];
                $stmt->execute($data);
                $rsp["success"] = true;
                $rsp['message'] = "Telah Berhasil Menambahkan Kota";
            }else{
        
                $rsp["error"] = false;
                $rsp['message'] = "Tidak Boleh ada Field Yang Kosong" ;
            }
        
            return $response
                ->withStatus(201)
                ->withJson($rsp);
            })->add($cekAPIKey);
    
        $app->put("/kota/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_kota = $request->getParsedBody();
            $sql = "UPDATE kota SET namaKota=:namaKota WHERE id_kota=:id";
            $stmt = $this->db->prepare($sql);
            $_data_1 = $new_kota['namaKota'];
            $rsp = array();
        if(!empty($_data_1)){
            $data = [
            ":id" => $id,
            ":namaKota" => $new_kota["namaKota"]
            ];
            $stmt->execute($data);
            $rsp["success"] = true;
            $rsp['message'] = "Telah Berhasil Update Kota";
            $mainCount=$stmt->rowCount();
            if($mainCount==0) {
                return $this->response->withJson(['status' => 'error', 'message' => 'Kota Tidak Ditemukan.'],200); 
            }
        }else{

            $rsp["error"] = false;
            $rsp['message'] = "Tidak Boleh ada Field Yang Kosong" ;
        }
    
        return $response
            ->withStatus(201)
            ->withJson($rsp);
        })->add($cekAPIKey);

        $app->delete("/kota/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM kota WHERE id_kota=:id";
            $stmt = $this->db->prepare($sql);
            $data = [
                ":id" => $id
            ];
            if($stmt->execute($data)) {
                $mainCount=$stmt->rowCount();
                if($mainCount==0) {
                    return $this->response->withJson(['status' => 'error', 'message' => 'Kota Tidak Ditemukan.'],200); 
                }
            }
            return $response->withJson(['status' => 'success', 'message' => 'Kota Telah Berhasil Dihapus.'], 200);
          
        })->add($cekAPIKey);
            

//kecamatan
        $app->get("/kecamatan/", function (Request $request, Response $response){
            $sql = "SELECT id_kecamatan, namaKota, namaKecamatan, kode_pos
            FROM kecamatan 
            INNER JOIN kota as a on (a.id_kota=kecamatan.id_kota)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 200);
        });
        
        $app->get("/kecamatan/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT id_kecamatan, namaKota, namaKecamatan, kode_pos
            FROM kecamatan 
            INNER JOIN kota as a on (a.id_kota=kecamatan.id_kota) WHERE id_kecamatan=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $mainCount=$stmt->rowCount();
            $result = $stmt->fetchAll();
            if($mainCount==0) {
                return $this->response->withJson(['status' => 'error', 'message' => 'Kecamatan Tidak Ditemukan.'],200); 
            }
            return $response->withJson(["status" => "success", "data" => $result], 200);
        })->add($cekAPIKey);

        $app->get("/kecamatan/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT * FROM kecamatan
            INNER JOIN kota as a on (a.id_kota=kecamatan.id_kota) WHERE namaKecamatan LIKE '%$keyword%' or namaKota LIKE '%$keyword%' or kode_pos LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if($result==null) {
                return $this->response->withJson(['status' => 'error', 'message' => 'Kecamatan Not Found.'],200); 
            }
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        })->add($cekAPIKey);

        $app->post("/kecamatan/", function (Request $request, Response $response){
            $new_kecamatan = $request->getParsedBody();
            $sql = "INSERT INTO kecamatan (namaKecamatan, id_kota, kode_pos) VALUE (:namaKecamatan, :id_kota, :kode_pos)";
            $stmt = $this->db->prepare($sql);
            $_data_1 = $new_kecamatan['namaKecamatan'];
            $_data_2 = $new_kecamatan['id_kota'];
            $_data_3 = $new_kecamatan['kode_pos'];
            $rsp = array();
            if(!empty($_data_1 && !empty($_data_2 && !empty($_data_3)))){
                $data = [
                    ":namaKecamatan" => $new_kecamatan["namaKecamatan"],
                    ":id_kota" => $new_kecamatan["id_kota"],
                    ":kode_pos" => $new_kecamatan["kode_pos"]
                    ];
                    $stmt->execute($data);
                    $rsp["success"] = true;
                    $rsp['message'] = "Telah Berhasil Menambahkan Kecamatan";
            }else{
                    $rsp["error"] = false;
                    $rsp['message'] = "Tidak Boleh Ada Field Yang Kosong" ;
            }
          
            
            return $response
            ->withStatus(201)
            ->withJson($rsp);
        })->add($cekAPIKey);

        $app->put("/kecamatan/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_kecamatan = $request->getParsedBody();
            $sql = "UPDATE kecamatan SET id_kota=:id_kota, namaKecamatan=:namaKecamatan, kode_pos=:kode_pos WHERE id_kecamatan=:id";
            $stmt = $this->db->prepare($sql);
            $_data_1 = $new_kecamatan['namaKecamatan'];
            $_data_2 = $new_kecamatan['id_kota'];
            $_data_3 = $new_kecamatan['kode_pos'];
            $rsp = array();
            if(!empty($_data_1 && !empty($_data_2 && !empty($_data_3)))){
                $data = [
                    ":id" => $id,
                    ":id_kota" => $new_kecamatan["id_kota"],
                    ":namaKecamatan" => $new_kecamatan["namaKecamatan"],
                    ":kode_pos" => $new_kecamatan["kode_pos"]
                ];
                $stmt->execute($data);
                $rsp["success"] = true;
                $rsp['message'] = "Success update Kecamatan";
                $mainCount=$stmt->rowCount();
                if($mainCount==0){
                    return $this->response->withJson(['status' => 'error', 'message' => 'kecamatan Tidak Ditermukan.'],200); 
                }
            }else{
                $rsp["error"] = false;
                $rsp['message'] = "Tidak Boleh Ada Field Yang Kosong" ;
            }
                return $response
                ->withStatus(201)
                ->withJson($rsp);
        })->add($cekAPIKey);

        $app->delete("/kecamatan/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM kecamatan WHERE id_kecamatan=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data)) {
                $mainCount=$stmt->rowCount();
                if($mainCount==0) {
                    return $this->response->withJson(['status' => 'error', 'message' => 'kecamatan Tidak Ditemukan.'],200); 
                }
            }
            return $response->withJson(['status' => 'success', 'message' => 'kecamatan Telah dihapus.'], 200);
        })->add($cekAPIKey);

        //kelurahan
        $app->get("/kelurahan/", function (Request $request, Response $response){
            $sql = "SELECT id_kelurahan, namaKelurahan, namaKota, namaKecamatan 
            FROM kelurahan
            INNER JOIN kota as a on (a.id_kota=kelurahan.id_kota)
            INNER JOIN kecamatan  as b on(b.id_kecamatan = kelurahan.id_kecamatan)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/kelurahan/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT id_kelurahan, namaKelurahan, namaKota, namaKecamatan
            FROM kelurahan 
            INNER JOIN kota as a on (a.id_kota=kelurahan.id_kota)
            INNER JOIN kecamatan  as b on(b.id_kecamatan = kelurahan.id_kecamatan) WHERE id_kelurahan=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $mainCount=$stmt->rowCount();
            $result = $stmt->fetchAll();
            if($mainCount==0) {
                return $this->response->withJson(['status' => 'error', 'message' => 'Kelurahan Tidak Ditemukan.'],200); 
            }
            return $response->withJson(["status" => "success", "data" => $result], 200);
        })->add($cekAPIKey);

        $app->get("/kelurahan/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT id_kelurahan, namaKelurahan, namaKecamatan, namaKota 
            FROM kelurahan 
            INNER JOIN kota as a on (a.id_kota=kelurahan.id_kota)
            INNER JOIN kecamatan  as b on(b.id_kecamatan = kelurahan.id_kecamatan) WHERE namaKecamatan LIKE '%$keyword%' or namaKota LIKE '%$keyword%' or namaKelurahan LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if($result==null) {
                return $this->response->withJson(['status' => 'error', 'message' => 'Kelurahan Not Found.'],200); 
            }
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        })->add($cekAPIKey);

        $app->post("/kelurahan/", function (Request $request, Response $response){
            $new_kelurahan = $request->getParsedBody();
            $sql = "INSERT INTO kelurahan (namaKelurahan, id_kota, id_kecamatan) VALUE (:namaKelurahan, :id_kota, :id_kecamatan)";
            $stmt = $this->db->prepare($sql);
            $_data_1 = $new_kelurahan['namaKelurahan'];
            $_data_2 = $new_kelurahan['id_kota'];
            $_data_3 = $new_kelurahan['id_kecamatan'];
            $rsp = array();
            if(!empty($_data_1 && !empty($_data_2 && !empty($_data_3)))){
                $data = [
                    ":namaKelurahan" => $new_kelurahan["namaKelurahan"],
                    ":id_kota" => $new_kelurahan["id_kota"],
                    ":id_kecamatan" => $new_kelurahan["id_kecamatan"]
                    ];
                    $stmt->execute($data);
                    $rsp["success"] = true;
                    $rsp['message'] = "Telah Berhasil Menambahkan Kelurahan";
            }else{
                    $rsp["error"] = false;
                    $rsp['message'] = "Tidak Boleh Ada Field Yang Kosong" ;
            }
          
            
            return $response
            ->withStatus(201)
            ->withJson($rsp);
        })->add($cekAPIKey);

        $app->put("/kelurahan/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_kelurahan = $request->getParsedBody();
            $sql = "UPDATE kelurahan SET id_kota=:id_kota, namaKelurahan=:namaKelurahan, id_kecamatan=:id_kecamatan WHERE id_kelurahan=:id";
            $stmt = $this->db->prepare($sql);
            $_data_1 = $new_kelurahan['namaKelurahan'];
            $_data_2 = $new_kelurahan['id_kota'];
            $_data_3 = $new_kelurahan['id_kecamatan'];
            $rsp = array();
            if(!empty($_data_1 && !empty($_data_2 && !empty($_data_3)))){
                $data = [
                    ":id" => $id,
                    ":id_kota" => $new_kelurahan["id_kota"],
                    ":namaKelurahan" => $new_kelurahan["namaKelurahan"],
                    ":id_kecamatan" => $new_kelurahan["id_kecamatan"]
                ];
                $stmt->execute($data);
                $rsp["success"] = true;
                $rsp['message'] = "Success update Kelurahan";
                $mainCount=$stmt->rowCount();
                if($mainCount==0){
                    return $this->response->withJson(['status' => 'error', 'message' => 'Kelurahan Tidak Ditermukan.'],200); 
                }
            }else{
                $rsp["error"] = false;
                $rsp['message'] = "Tidak boleh ada field yang kosong" ;
            }
                return $response
                ->withStatus(201)
                ->withJson($rsp);
        })->add($cekAPIKey);
      
        $app->delete("/kelurahan/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM kelurahan WHERE id_kelurahan=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data)) {
                $mainCount=$stmt->rowCount();
                if($mainCount==0) {
                    return $this->response->withJson(['status' => 'error', 'message' => 'Kelurahan Tidak Ditemukan.'],200); 
                }
            }
            return $response->withJson(['status' => 'success', 'message' => 'Kelurahan Telah dihapus.'], 200);
        })->add($cekAPIKey);


        //Kampus
        $app->get("/kampus/", function (Request $request, Response $response){
            $sql = "SELECT id_kampus, namaKampus, alamatKampus, namaKota, namaKecamatan, namaKelurahan
            FROM kampus
            INNER JOIN kota as a on (a.id_kota=kampus.id_kota)
            INNER JOIN kecamatan  as b on(b.id_kecamatan = kampus.id_kecamatan)
            INNER JOIN kelurahan  as c on(c.id_kelurahan = kampus.id_kelurahan)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        });

        $app->get("/kampus/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT id_kampus, namaKampus, alamatKampus, namaKota, namaKecamatan, namaKelurahan
            FROM kampus
            INNER JOIN kota as a on (a.id_kota=kampus.id_kota)
            INNER JOIN kecamatan  as b on(b.id_kecamatan = kampus.id_kecamatan)
            INNER JOIN kelurahan  as c on(c.id_kelurahan = kampus.id_kelurahan) WHERE id_kampus=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $mainCount=$stmt->rowCount();
            $result = $stmt->fetchAll();
            if($mainCount==0) {
                return $this->response->withJson(['status' => 'error', 'message' => 'Kampus Tidak Ditemukan.'],200); 
            }
            return $response->withJson(["status" => "success", "data" => $result], 200);
        })->add($cekAPIKey);

        $app->get("/kampus/search/", function (Request $request, Response $response, 
        $args){
            $keyword = $request->getQueryParam("keyword"); $sql = "SELECT id_kampus, namaKampus, alamatKampus, namaKota, namaKecamatan, namaKelurahan
            FROM kampus
            INNER JOIN kota as a on (a.id_kota=kampus.id_kota)
            INNER JOIN kecamatan  as b on(b.id_kecamatan = kampus.id_kecamatan)
            INNER JOIN kelurahan  as c on(c.id_kelurahan = kampus.id_kelurahan) WHERE namaKota LIKE '%$keyword%' or namaKecamatan LIKE '%$keyword%' or namaKelurahan LIKE '%$keyword%' or alamatKampus LIKE '%$keyword%' or namaKampus LIKE '%$keyword%'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            if($result==null) {
                return $this->response->withJson(['status' => 'error', 'message' => 'Kampus Not Found.'],200); 
            }
            return $response->withJson(["status" => "success", "data" => $result], 
        200);
        })->add($cekAPIKey);

        $app->post("/kampus/", function (Request $request, Response $response){
            $new_kampus = $request->getParsedBody();
            $sql = "INSERT INTO kampus (namaKampus, alamatKampus, id_kota, id_kecamatan, id_kelurahan) VALUE (:namaKampus, :alamatKampus, :id_kota, :id_kecamatan, :id_kelurahan)";
            $stmt = $this->db->prepare($sql);
            $_data_1 = $new_kampus['namaKampus'];
            $_data_2 = $new_kampus['alamatKampus'];
            $_data_3 = $new_kampus['id_kota'];
            $_data_4 = $new_kampus['id_kecamatan'];
            $_data_5 = $new_kampus['id_kelurahan'];
            $rsp = array();
            if(!empty($_data_1 && !empty($_data_2 && !empty($_data_3 && !empty($_data_4 && !empty($_data_5)))))){
                $data = [
                    ":namaKampus" => $new_kampus["namaKampus"],
                    ":alamatKampus" => $new_kampus["alamatKampus"],
                    ":id_kota" => $new_kampus["id_kota"],
                    ":id_kecamatan" => $new_kampus["id_kecamatan"],
                    ":id_kelurahan" => $new_kampus["id_kelurahan"]
                    ];
                    $stmt->execute($data);
                    $rsp["success"] = true;
                    $rsp['message'] = "Telah Berhasil Menambahkan Kampus";
            }else{
                    $rsp["error"] = false;
                    $rsp['message'] = "Tidak Boleh Ada Field Yang Kosong" ;
            }
          
            
            return $response
            ->withStatus(201)
            ->withJson($rsp);
        })->add($cekAPIKey);

        $app->put("/kampus/{id}", function (Request $request, Response $response, $args) {
            $id = $args["id"];
            $new_kampus = $request->getParsedBody();
            $sql = "UPDATE kampus SET namaKampus=:namaKampus, alamatKampus=:alamatKampus, id_kota=:id_kota, id_kecamatan=:id_kecamatan, id_kelurahan=:id_kelurahan WHERE id_kampus=:id";
            $stmt = $this->db->prepare($sql);
            $_data_1 = $new_kampus['namaKampus'];
            $_data_2 = $new_kampus['alamatKampus'];
            $_data_3 = $new_kampus['id_kota'];
            $_data_4 = $new_kampus['id_kecamatan'];
            $_data_5 = $new_kampus['id_kelurahan'];
            $rsp = array();
            if(!empty($_data_1 && !empty($_data_2 && !empty($_data_3 && !empty($_data_4 && !empty($_data_5)))))){
                $data = [
                    ":id" => $id,
                    ":namaKampus" => $new_kampus["namaKampus"],
                    ":alamatKampus" => $new_kampus["alamatKampus"],
                    ":id_kota" => $new_kampus["id_kota"],
                    ":id_kecamatan" => $new_kampus["id_kecamatan"],
                    ":id_kelurahan" => $new_kampus["id_kelurahan"]
                ];
                $stmt->execute($data);
                $rsp["success"] = true;
                $rsp['message'] = "Success update Kampus";
                $mainCount=$stmt->rowCount();
                if($mainCount==0){
                    return $this->response->withJson(['status' => 'error', 'message' => 'Kampu Tidak Ditermukan.'],200); 
                }
            }else{
                $rsp["error"] = false;
                $rsp['message'] = "Tidak boleh ada field yang kosong" ;
            }
                return $response
                ->withStatus(201)
                ->withJson($rsp);
        })->add($cekAPIKey);

        $app->delete("/kampus/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM kampus WHERE id_kampus=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id
            ];
        
            if($stmt->execute($data)) {
                $mainCount=$stmt->rowCount();
                if($mainCount==0) {
                    return $this->response->withJson(['status' => 'error', 'message' => 'Kampus Tidak Ditemukan.'],200); 
                }
            }
            return $response->withJson(['status' => 'success', 'message' => 'Kampus Telah dihapus.'], 200);
        })->add($cekAPIKey);

    });
};  