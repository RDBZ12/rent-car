<?php
class UsuariosModel extends Query{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUsuario(string $usuario, string $clave)
    {
        // Using the user's provided column names: usuario, clave, estado
        // Note: 'estado' in schema is INT (1 for admin in sample), but could be used as string by controller.
        // I will allow both for safety or just check equality.
        $sql = "SELECT id, usuario, nombre, apellido, perfil FROM usuarios WHERE usuario = '$usuario' AND clave = '$clave' AND estado = 1";
        $data = $this->select($sql);
        return $data;
    }

    public function getUsuarios(int $estado)
    {
        $sql = "SELECT id, usuario, nombre, apellido, perfil AS rol, estado FROM usuarios WHERE estado = $estado";
        $data = $this->selectAll($sql);
        return $data;
    }

    public function getAllUsuarios()
    {
        $sql = "SELECT id, usuario, nombre, apellido, perfil AS rol, estado FROM usuarios";
        $data = $this->selectAll($sql);
        return $data;
    }

    public function registrarUsuario(string $usuario, string $nombre, string $apellido, string $clave, string $perfil)
    {
        $verificar = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
        $existe = $this->select($verificar);
        if (empty($existe)) {
            $hash = hash("SHA256", $clave);
            $sql = "INSERT INTO usuarios(usuario, nombre, apellido, clave, perfil, estado) VALUES (?,?,?,?,?,1)";
            $datos = array($usuario, $nombre, $apellido, $hash, $perfil);
            $data = $this->save($sql, $datos);
            if ($data == 1) {
                $res = "ok";
            } else {
                $res = "error";
            }
        } else {
            $res = "existe";
        }
        return $res;
    }

    public function modificarUsuario(string $usuario, string $nombre, string $apellido, string $perfil, int $id)
    {
        $sql = "UPDATE usuarios SET usuario = ?, nombre = ?, apellido = ?, perfil = ? WHERE id = ?";
        $datos = array($usuario, $nombre, $apellido, $perfil, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function modificarUsuarioConClave(string $usuario, string $nombre, string $apellido, string $clave, string $perfil, int $id)
    {
        $hash = hash("SHA256", $clave);
        $sql = "UPDATE usuarios SET usuario = ?, nombre = ?, apellido = ?, clave = ?, perfil = ? WHERE id = ?";
        $datos = array($usuario, $nombre, $apellido, $hash, $perfil, $id);
        $data = $this->save($sql, $datos);
        if ($data == 1) {
            $res = "modificado";
        } else {
            $res = "error";
        }
        return $res;
    }

    public function editarUser(int $id)
    {
        $sql = "SELECT *, perfil AS rol FROM usuarios WHERE id = $id";
        $data = $this->select($sql);
        return $data;
    }

    public function getPass(string $clave, int $id)
    {
        $sql = "SELECT * FROM usuarios WHERE clave = '$clave' AND id = $id";
        $data = $this->select($sql);
        return $data;
    }

    public function accionUser(int $estado, int $id)
    {
        $sql = "UPDATE usuarios SET estado = ? WHERE id = ?";
        $datos = array($estado, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }

    public function modificarPass(string $clave, int $id)
    {
        $sql = "UPDATE usuarios SET clave = ? WHERE id = ?";
        $datos = array($clave, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }

    public function modificarDato(string $usuario, string $nombre, string $apellido, string $correo, string $telefono, string $direccion, string $perfil, int $id)
    {
        $sql = "UPDATE usuarios SET usuario = ?, nombre = ?, apellido = ?, correo = ?, telefono = ?, direccion = ?, perfil = ? WHERE id = ?";
        $datos = array($usuario, $nombre, $apellido, $correo, $telefono, $direccion, $perfil, $id);
        $data = $this->save($sql, $datos);
        return $data;
    }
}
?>