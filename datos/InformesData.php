<?php 
    function FnRegistrarInforme($conmy, $orden, $cliente, $equipo, $fecha, $actividad, $usuario) {
        try {
            $stmt = $conmy->prepare("CALL spman_agregarinforme(:_ordid, :_equid, :_cliid, :_fecha, :_ordnombre, :_clinombre, :_clicontacto, :_ubicacion, :_supervisor, :_equcodigo, :_equnombre, :_equmarca, :_equmodelo, :_equserie, :_equdatos, :_equkm, :_equhm, :_actividad, :_usuario, @_id)");
            $stmt->bindParam(':_ordid', $orden->id, PDO::PARAM_INT);
            $stmt->bindParam(':_equid', $equipo->id, PDO::PARAM_INT);
            $stmt->bindParam(':_cliid', $cliente->id, PDO::PARAM_INT);
            $stmt->bindParam(':_fecha', $fecha, PDO::PARAM_STR);
            $stmt->bindParam(':_ordnombre', $orden->nombre, PDO::PARAM_STR);
            $stmt->bindParam(':_clinombre', $cliente->nombre, PDO::PARAM_STR);
            $stmt->bindParam(':_clicontacto', $orden->contacto, PDO::PARAM_STR);
            $stmt->bindParam(':_ubicacion', $equipo->ubicacion, PDO::PARAM_STR);
            $stmt->bindParam(':_supervisor', $orden->supervisor, PDO::PARAM_STR);
            $stmt->bindParam(':_equcodigo', $equipo->codigo, PDO::PARAM_STR);
            $stmt->bindParam(':_equnombre', $equipo->nombre, PDO::PARAM_STR);
            $stmt->bindParam(':_equmarca', $equipo->marca, PDO::PARAM_STR);
            $stmt->bindParam(':_equmodelo', $equipo->modelo, PDO::PARAM_STR);
            $stmt->bindParam(':_equserie', $equipo->serie, PDO::PARAM_STR);
            $stmt->bindParam(':_equdatos', $equipo->caracteristicas, PDO::PARAM_STR);
            $stmt->bindParam(':_equkm', $orden->km, PDO::PARAM_INT);
            $stmt->bindParam(':_equhm', $orden->hm, PDO::PARAM_INT);
            $stmt->bindParam(':_actividad', $actividad, PDO::PARAM_STR);
            $stmt->bindParam(':_usuario', $usuario, PDO::PARAM_STR);
            $stmt->execute();

            $stmt = $conmy->query("SELECT @_id as id");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id = $row['id'];

            return $id;
            
        } catch (PDOException $e) {
            throw new Exception("Error en la Marcación: ".$e->getMessage());//sera propagado al catch(Exception $ex) del nivel superior.
        }
    }

    function FnModificarInforme($conmy, $informe) {
        try {
            $res=false;
            $stmt = $conmy->prepare("update tblinforme set fecha=:Fecha, cli_contacto=:CliContacto, ubicacion=:Ubicacion, supervisor=:Supervisor, actualizacion=:Actualizacion;");
            $params = array(':Fecha'=>$informe->fecha, ':CliContacto'=>$informe->clicontacto, ':Ubicacion'=>$informe->ubicacion, ':Supervisor'=>$informe->supervisor, ':Actualizacion'=>$informe->actualizacion);
            if($stmt->execute($params)){
                $res=true;
            }
            return $res;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    function FnModificarInformeDatosGenerales($conmy, $informe) {
        try {
            $res = false;
            $stmt = $conmy->prepare("UPDATE tblinforme SET fecha = :Fecha, cli_contacto = :CliContacto, ubicacion = :Ubicacion, supervisor = :Supervisor, actualizacion = :Actualizacion WHERE id=:Id");
            $params = array(
                ':Fecha' => $informe->fecha,
                ':CliContacto' => $informe->clicontacto,
                ':Ubicacion' => $informe->ubicacion,
                ':Supervisor' => $informe->supervisor,
                ':Actualizacion' => $informe->actualizacion,
                ':Id' => $informe->id,
            );
            if ($stmt->execute($params)) {
                $res = true;
            }
            return $res;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    function FnModificarInformeDatosEquipos($conmy, $informe) {
        try {
            $res = false;
            $stmt = $conmy->prepare("update 
            tblinforme set 
            equ_nombre = :EquNombre, 
            equ_marca = :EquMarca, 
            equ_modelo = :EquModelo, 
            equ_serie = :EquSerie, 
            equ_km = :EquKm, 
            equ_hm = :EquHm, 
            actualizacion = :Actualizacion where id=:Id;");
            $params = array(
              ':EquNombre' => $informe->equnombre,
              ':EquMarca' => $informe->equmarca,
              ':EquModelo' => $informe->equmodelo,
              ':EquSerie' => $informe->equserie,
              ':EquKm' => $informe->equkm,
              ':EquHm' => $informe->equhm,
              ':Actualizacion' => $informe->actualizacion,
              ':Id' => $informe->id 
            );
            if ($stmt->execute($params)) {
                $res = true;
            }
            return $res;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    
    // BUSCAR INFORME MATRIZ
    function FnBuscarInformeMatriz($conmy, $id, $cliid) {
      try {
        $stmt = $conmy->prepare("SELECT id, ordid, equid, cliid, numero, nombre, fecha, ord_nombre, cli_nombre, cli_contacto, ubicacion, supervisor, equ_codigo, equ_nombre, equ_marca, equ_modelo, equ_serie, equ_datos, equ_km, equ_hm, actividad, estado FROM tblinforme WHERE id = :Id AND cliid = :Cliid");
        $stmt->execute(array(':Id' => $id, ':Cliid' => $cliid));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $informe = new stdClass();
            $informe->id = $row['id'];
            $informe->ordid = $row['ordid'];
            $informe->equid = $row['equid'];
            $informe->cliid = $row['cliid'];
            $informe->numero = $row['numero'];
            $informe->nombre = $row['nombre'];
            $informe->fecha = $row['fecha'];
            $informe->ordnombre = $row['ord_nombre'];
            $informe->clinombre = $row['cli_nombre'];
            $informe->clicontacto = $row['cli_contacto'];
            $informe->ubicacion = $row['ubicacion'];
            $informe->supervisor = $row['supervisor'];
            $informe->equcodigo = $row['equ_codigo'];
            $informe->equnombre = $row['equ_nombre'];
            $informe->equmarca = $row['equ_marca'];
            $informe->equmodelo = $row['equ_modelo'];
            $informe->equserie = $row['equ_serie'];
            $informe->equdatos = $row['equ_datos'];
            $informe->equkm = $row['equ_km'];
            $informe->equhm = $row['equ_hm'];
            $informe->actividad = $row['actividad'];
            $informe->estado = $row['estado'];

            return $informe;
        } else {
            throw new Exception('Informe no disponible para el cliente.');
        }
      } catch (PDOException $ex) {
          throw new Exception('Error al buscar Informe: ' . $ex->getMessage());
      } catch (Exception $ex) {
          throw new Exception($ex->getMessage());
      }
    }
  

    //BUSCAR ACTIVIDAD POR INFORME
    function FnBuscarActividadPorInforme($conmy, $id){
      try {
        $stmt = $conmy->prepare("SELECT id, actividad, estado FROM tblinforme WHERE id=:Id;");
        $stmt->execute(array(':Id' => $id));
        $informe = new stdClass();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $informe->id = $row['id'];
          $informe->actividad = $row['actividad'];
          $informe->estado = $row['estado'];
        }
        return $informe;
      } catch (PDOException $ex) {
        throw new Exception('Error al buscar Informe: ' .$e->getMessage());
      }
    }

    // BUSCAR SUPERVISORES
    function FnBuscarSupervisores($comy, $id) {
      try {
        $stmt = $comy->prepare("SELECT idsupervisor, idcliente, supervisor FROM cli_supervisores WHERE idcliente=:Id");
        $stmt->execute(array(':Id'=>$id));
        $supervisores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $supervisores;
      } catch (PDOException $e) {
        throw new Exception('Error al buscar supervisores: ' . $e->getMessage());
      }
    }

    // CONSULTAR INFORMES Y ARCHIVOS POR ID
    function FnBuscarArchivos($conmy, $id) {
      try {
          // $stmt = $conmy->prepare("SELECT * FROM tblarchivos WHERE refid=:Id AND tabla='INF'");
          $stmt = $conmy->prepare("SELECT id, refid, tabla, nombre, descripcion, tipo, estado, titulo FROM tblarchivos WHERE refid=:Id");
          $stmt->execute(array(':Id' => $id));
          $archivos = $stmt ->fetchAll(PDO::FETCH_ASSOC);
          return $archivos;
      } catch (PDOException $ex) {
          throw new Exception($e->getMessage());
      }
    }

    // BUSCAR EQUIPO
    function FnBuscarEquipo($conmy, $id) {
        try {
            $stmt = $conmy->prepare("select idactivo, codigo, activo, grupo, marca, modelo, serie, anio, fabricante, procedencia, caracteristicas, ubicacion from man_activos where idactivo=:Id;");
            $stmt->execute(array(':Id'=>$id));
            $equipo = new stdClass();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $equipo->id = $row['idactivo'];
                $equipo->codigo = $row['codigo'];
                $equipo->nombre = $row['activo'];
                $equipo->flota = $row['grupo'];
                $equipo->marca = $row['marca'];
                $equipo->modelo = $row['modelo'];
                $equipo->serie = $row['serie'];
                $equipo->anio = $row['anio'];
                $equipo->fabricante = $row['fabricante'];
                $equipo->procedencia = $row['procedencia'];
                $equipo->datos = $row['caracteristicas'];
                $equipo->ubicacion = $row['ubicacion'];
            }
            return $equipo;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }


    // LISTAR EQUIPOS
    function FnBuscarEquipos($conmy, $nombre, $cliId) {
      $sql = "SELECT idactivo, activo FROM man_activos WHERE idcliente = :cliId";
      if (!empty($nombre)) {
          $sql .= " AND activo LIKE :search";
      }
      $stmt = $conmy->prepare($sql);
      $params = [':cliId' => $cliId];
      if (!empty($nombre)) {
          $params[':search'] = "%$nombre%";
      }
      $stmt->execute($params);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  
    // Buscar actividad por Id
    function FnBuscarActividad($conmy, $id) {
        try {
            $stmt = $conmy->prepare("SELECT id, infid, ownid, tipo, actividad, diagnostico, trabajos, observaciones, estado FROM tbldetalleinforme WHERE id = :Id;");
            $stmt->execute([':Id' => $id]);
            $actividad = new stdClass();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $actividad->id = $row['id'];
                $actividad->infid = $row['infid'];
                $actividad->ownid = $row['ownid'];
                $actividad->tipo = $row['tipo'];
                $actividad->actividad = $row['actividad'];
                $actividad->diagnostico = $row['diagnostico'];
                $actividad->trabajos = $row['trabajos'];
                $actividad->observaciones = $row['observaciones'];
                $actividad->estado = $row['estado'];
            }
            return $actividad;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    function FnBuscarActividades($conmy, $infid) {
        try {
            $stmt = $conmy->prepare("SELECT id, ownid, tipo, actividad, diagnostico, trabajos, observaciones, estado FROM tbldetalleinforme WHERE infid = :Infid;");
            $stmt->execute(array(':Infid' => $infid));
            $actividades = $stmt ->fetchAll(PDO::FETCH_ASSOC);;
            return $actividades;
        } catch (PDOException $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    function FnBuscarOrden($conmy, $id) {
        try {
            $stmt = $conmy->prepare("select idot, idactivo, idcliente, ot, km, hm, supervisor, contacto, estado from man_ots where idot=:Id;");
            $stmt->execute(array(':Id'=>$id));
            $orden = new stdClass();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $orden->id = $row['idot'];
                $orden->equid = $row['idactivo'];
                $orden->cliid = $row['idcliente'];
                $orden->nombre = $row['ot'];
                $orden->km = $row['km'];
                $orden->hm = $row['hm'];
                $orden->supervisor = $row['supervisor'];
                $orden->contacto = $row['contacto'];
                $orden->estado = $row['estado'];
            }
            return $orden;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    function FnBuscarCliente($conmy, $id) {
        try {
            $stmt = $conmy->prepare("select idcliente, ruc, razonsocial, nombre, estado from man_clientes where idcliente=:Id;");
            $stmt->execute(array(':Id'=>$id));
            $cliente = new stdClass();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cliente->id = $row['idcliente'];
                $cliente->ruc = $row['ruc'];
                $cliente->nombre = $row['razonsocial'];
                $cliente->alias = $row['nombre'];
                $cliente->estado = $row['estado'];
            }
            return $cliente;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    function FnRegistrarActividad($conmy, $actividad) {
        try {
            $res = false;
            $stmt = $conmy->prepare("INSERT INTO tbldetalleinforme (infid, ownid, actividad, diagnostico, trabajos, observaciones, tipo, creacion, actualizacion) VALUES (:InfId, :OwnId, :Actividad, :Diagnostico, :Trabajos, :Observaciones, :Tipo,:Creacion, :Actualizacion);");
            $params = array(':InfId' => $actividad->infid,':OwnId' => $actividad->ownid,':Actividad' => $actividad->actividad,':Diagnostico' => $actividad->diagnostico,':Trabajos' => $actividad->trabajos,':Observaciones' => $actividad->observaciones,':Tipo' => $actividad->tipo, ':Creacion' => $actividad->usuario,':Actualizacion' => $actividad->usuario);
            if ($stmt->execute($params)) {
                $res = true;
            }
            return $res;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
        
    function FnModificarActividad($conmy, $actividad) {
        try {
            $res=false;
            $stmt = $conmy->prepare("update tbldetalleinforme set actividad=:Actividad, diagnostico=:Diagnostico, trabajos=:Trabajos, observaciones=:Observaciones, actualizacion=:Actualizacion where id=:Id;");
            $params = array(':Actividad'=>$actividad->actividad, ':Diagnostico'=>$actividad->diagnostico, ':Trabajos'=>$actividad->trabajos, ':Observaciones'=>$actividad->observaciones, ':Actualizacion'=>$actividad->usuario, ':Id'=>$actividad->id);
            if($stmt->execute($params)){
                $res=true;
            }
            return $res;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    function FnModificarActividadInforme($conmy, $actividad) {
        try {
            $res = false;
            $stmt = $conmy->prepare("UPDATE tblinforme SET actividad = :Actividad WHERE id = :Id;");
            $params = array(':Actividad' => $actividad->actividad, ':Id' => $actividad->id);
            if ($stmt->execute($params)) {
                $res = true;
            }
            return $res;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
         
    function FnEliminarActividad($conmy, $id) {
        try {
            $res = false;
            // Corregir la consulta SQL eliminando el paréntesis extra
            $stmt = $conmy->prepare("DELETE FROM tbldetalleinforme WHERE id = :Id");
            $params = array(':Id' => $id);
            if ($stmt->execute($params)) {
                $res = true;
            }
            return $res;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    function FnRegistrarImagen($conmy, $imagen) {
        try {
            $res=false;
            $stmt = $conmy->prepare("insert into tblarchivos(refid, tabla, nombre, titulo, descripcion, tipo, actualizacion) values(:RefId, :Tabla, :Nombre, :Titulo, :Descripcion, :Tipo, :Actualizacion);");
            $params = array(':RefId'=>$imagen->refid, ':Tabla'=>$imagen->tabla, ':Nombre'=>$imagen->nombre, ':Titulo'=>$imagen->titulo, ':Descripcion'=>$imagen->descripcion, ':Tipo'=>$imagen->tipo, ':Actualizacion'=>$imagen->usuario);
            if($stmt->execute($params)){
                $res=true;
            }
            return $res;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    function FnEliminarArchivo($conmy, $id) {
        try {
            $res = false;
            $stmt = $conmy->prepare("DELETE FROM tblarchivos WHERE id =:Id");
            $params = array(':Id' => $id);
            if ($stmt->execute($params)) {
                $res = true;
            }
            return $res;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
    
    function FnBuscarInformes($conmy, $informe) {
      try {
          $informes = ['data' => [], 'pag' => 0];
          $query = " WHERE cliid = :CliId";
          $params = [':CliId' => $informe->cliid];
          if (!empty($informe->nombre)) {
              $query .= " AND nombre LIKE :Nombre";
              $params[':Nombre'] = "%" . $informe->nombre . "%";
          }
  
          if (!empty($informe->equid)) {
              $query .= " AND equid = :Equid";
              $params[':Equid'] = $informe->equid;
          }
          $query .= " AND fecha BETWEEN :FechaInicial AND :FechaFinal";
          $params[':FechaInicial'] = $informe->fechainicial;
          $params[':FechaFinal'] = $informe->fechafinal;
  
          // Calcular el desplazamiento para la paginación
          $offset = $informe->pagina * 20;
  
          $stmt = $conmy->prepare("SELECT id, nombre, fecha, cli_nombre, equ_codigo, actividad, estado FROM tblinforme" . $query . " LIMIT :Offset, 20");
  
          // Bind de los parámetros
          foreach ($params as $key => $value) {
              $stmt->bindValue($key, $value);
          }
          $stmt->bindValue(':Offset', $offset, PDO::PARAM_INT);
  
          $stmt->execute();
          $n = $stmt->rowCount();
  
          if ($n > 0) {
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  $informes['data'][] = [
                      'id' => (int)$row['id'],
                      'nombre' => $row['nombre'],
                      'fecha' => $row['fecha'],
                      'clinombre' => $row['cli_nombre'],
                      'equcodigo' => $row['equ_codigo'],
                      'actividad' => $row['actividad'],
                      'estado' => (int)$row['estado']
                  ];
              }
              // Calcular el número total de páginas
              $totalQuery = "SELECT COUNT(*) FROM tblinforme" . $query;
              $totalStmt = $conmy->prepare($totalQuery);
              foreach ($params as $key => $value) {
                  $totalStmt->bindValue($key, $value);
              }
              $totalStmt->execute();
              $totalRows = $totalStmt->fetchColumn();
              $informes['pag'] = ceil($totalRows / 20);
          }
  
          return $informes;
      } catch (PDOException $e) {
          throw new Exception($e->getMessage());
      }
    }



?>