-- Script para actualizar las contraseñas de los usuarios de ejemplo
-- Ejecutar este script si ya tiene la base de datos instalada y necesita actualizar las contraseñas

USE sistema_notas;

-- Actualizar contraseña del administrador (usuario: admin, password: admin123)
UPDATE administradores
SET password = '$2y$12$8c8heh16VHp53mGrrJgareH1AkimyzQT5T3PF6kmdW2K2m.5KgJxy'
WHERE usuario = 'admin';

-- Actualizar contraseñas de padres (password: el mismo DNI)
UPDATE padres
SET password = '$2y$12$esaYJtzh4omKrdluX4rim.qb2quG96QoFk.JQxd.Lre.T1sCu4Eza'
WHERE dni = '12345678';

UPDATE padres
SET password = '$2y$12$bBFgcc9q4SO1FtNU4hvaYehHwFePvp9eVPo9FiR0nxqO2NINKe2P6'
WHERE dni = '87654321';

SELECT 'Contraseñas actualizadas correctamente' as resultado;
