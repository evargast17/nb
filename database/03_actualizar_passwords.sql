-- =====================================================
-- ACTUALIZAR PASSWORDS CON HASHES CORRECTOS
-- Ejecuta este script si las contraseñas no funcionan
-- =====================================================

USE sistema_notas;

-- Actualizar contraseña del administrador
-- Usuario: admin | Password: admin123
UPDATE administradores
SET password = '$2y$12$h9.EN1jFMgV7qRX30Wup6O2w/tkKbA7f0Sa9o.ySP0PbLIxwZbcXi'
WHERE usuario = 'admin';

-- Actualizar contraseñas de padres (password = su DNI)
UPDATE padres SET password = '$2y$12$BZ1y/c3lwtL9rWcoGPx9iuCILCmzqp/L3Jh5DVTlEatTBeW/Thczu' WHERE dni = '12345678';
UPDATE padres SET password = '$2y$12$mxdpEJv0cFItwAblJbc1Pe19P3Lmho/OPsBvKMW/GMYZZPvkBT/ci' WHERE dni = '23456789';
UPDATE padres SET password = '$2y$12$mUMtzfybD0Jwc54bdE883.nQ55oiP.hbkgWoWxHanLWV3wKjArWka' WHERE dni = '34567890';
UPDATE padres SET password = '$2y$12$7sMSWijhrRJSyT.QTIva3ecsupivSX4B2jloHa4qyogBZZuEioEPK' WHERE dni = '45678901';
UPDATE padres SET password = '$2y$12$2lGasJuBVMrxGxxKyX1z8.EqQL3xSQMrWKsUV3K.SBG9OPjx4qjuK' WHERE dni = '56789012';

SELECT 'Contraseñas actualizadas correctamente' as mensaje;

-- Verificar
SELECT 'Administrador actualizado:' as info, usuario FROM administradores WHERE usuario = 'admin';
SELECT 'Padres actualizados:' as info, COUNT(*) as total FROM padres WHERE dni IN ('12345678', '23456789', '34567890', '45678901', '56789012');
