CREATE DATABASE CursosDBN;

drop database CursosDBN;

USE CursosDBN;

-- Tabla Estado
CREATE TABLE Estado (
    ID_estado INT AUTO_INCREMENT PRIMARY KEY,
    Descripcion VARCHAR(255),
    Progreso_curso DECIMAL(5,2)
);



-- Tabla Cursante
CREATE TABLE Cursante (
    Matricula bigINT PRIMARY KEY,
    Nombre VARCHAR(255),
    Apellido_paterno VARCHAR(255),
    Apellido_materno VARCHAR(255),
    Correo VARCHAR(255),
    Telefono VARCHAR(15),
    Estado INT,
    FOREIGN KEY (Estado) REFERENCES Estado(ID_estado)
);

select * from `Curso`

insert into Cursante(Nombre,`Apellido_Paterno`)
values ('ericpepek', 'marpepetinez')

-- Tabla Capacitor
CREATE TABLE Capacitor (
    Codigo_capacitador bigINT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(255),
    Primer_apellido VARCHAR(255),
    Segundo_apellido VARCHAR(255),
    Numero_celular VARCHAR(15),
    Correo VARCHAR(255)
);

select * from Test

insert into Capacitor(Nombre,Primer_apellido,Segundo_apellido,Numero_celular,Correo,`Password`)
values('erick','martinez','morales', '2222222222',"rederick@gmail.com","erick")

-- Tabla Tema
CREATE TABLE Tema (
    Codigo_tema INT AUTO_INCREMENT PRIMARY KEY,
    Descripcion_tema VARCHAR(255),
    Nombre_tema VARCHAR(255)
);

-- Tabla Curso
CREATE TABLE Curso (
    Codigo_curso INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_curso VARCHAR(255),
    Descripcion_curso TEXT,
    Capacitador bigINT,
    Tema INT,
    FOREIGN KEY (Capacitador) REFERENCES Capacitor(Codigo_capacitador),
    FOREIGN KEY (Tema) REFERENCES Tema(Codigo_tema)
);

select * from Tema

INSERT INTO Test (Nombre_test, Descripcion_test, Fecha_limite, Puntaje, Tipo_test, Curso)
VALUES ('Examen Final', 'Examen del curso', '2024-12-31', 100, NULL, 2024000001);



-- Tabla Registro_curso
CREATE TABLE Registro_curso (
    Folio_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
    Fecha_inscripcion DATE,
    Fecha_inicio DATE,
    Fecha_fin DATE,
    Estado INT,
    Calificaciones DECIMAL(5,2),
    Progreso DECIMAL(5,2),
    Cursante bigINT,
    Curso INT,
    FOREIGN KEY (Estado) REFERENCES Estado(ID_estado),
    FOREIGN KEY (Cursante) REFERENCES Cursante(Matricula),
    FOREIGN KEY (Curso) REFERENCES Curso(Codigo_curso)
);

select * from Registro_curso

insert into Estado(Descripcion)
values ("availed")

-- Tabla Certificación

CREATE TABLE Certificacion (
    Folio_certificacion INT AUTO_INCREMENT PRIMARY KEY,
    Desc_certificacion TEXT,
    Titulo_certificacion VARCHAR(255),
    Fecha_expedicion DATE,
    Cursante BIgINT,
    Test INT,
    FOREIGN KEY (Cursante) REFERENCES Cursante(Matricula),
    FOREIGN KEY (Test) REFERENCES Test(Codigo_test)
);

select * from `Certificacion`

-- Tabla Tipo_test
CREATE TABLE Tipo_test (
    Codigo_tipotest INT AUTO_INCREMENT PRIMARY KEY,
    Descripcion VARCHAR(255)
);

INSERT INTO Tipo_test (Descripcion) VALUES ('Examen'), ('Cuestionario'), ('Práctica'), ('Proyecto');


select * from `Cursante`

select * from Tipo_test

-- Tabla Test
CREATE TABLE Test (
    Codigo_test INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_test VARCHAR(255),
    Descripcion_test TEXT,
    Fecha_limite DATE,
    Puntaje DECIMAL(5,2),
    Tipo_test INT,
    Curso INT,
    FOREIGN KEY (Tipo_test) REFERENCES Tipo_test(Codigo_tipotest),
    FOREIGN KEY (Curso) REFERENCES Curso(Codigo_curso)
);

insert into Test(Nombre_test, Puntaje)
values ("Matematicas", 100)

-- Tabla Pregunta
CREATE TABLE Pregunta (
    Num_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    Texto_pregunta TEXT,
    Test INT,
    FOREIGN KEY (Test) REFERENCES Test(Codigo_test)
);

-- Tabla Respuestas
CREATE TABLE Respuestas (
    Numero_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    Descripcion TEXT,
    Puntaje_respuesta DECIMAL(5,2),
    Pregunta INT,
    FOREIGN KEY (Pregunta) REFERENCES Pregunta(Num_pregunta)
);


select * from `Respuestas`


-- Tabla Material_apoyo
CREATE TABLE Material_apoyo (
    Codigo_material INT AUTO_INCREMENT PRIMARY KEY,
    Descripcion TEXT,
    Nombre_material VARCHAR(255),
    Estado INT,
    Tema INT,
    FOREIGN KEY (Estado) REFERENCES Estado(ID_estado),
    FOREIGN KEY (Tema) REFERENCES Tema(Codigo_tema)
);

ALTER TABLE Cursante ADD Password VARCHAR(255);
ALTER TABLE Capacitor ADD Password VARCHAR(255);

select * from `Cursante`

select * from Pregunta

select * from `Capacitor`

select * from `Curso`

select * from Registro_curso

/*pruebas */

INSERT INTO Estado (Descripcion, Progreso_curso) 
VALUES ('Completo', 100.00);

INSERT INTO Cursante (Nombre, Apellido_paterno, Apellido_materno, Correo, Telefono, Estado) 
VALUES ('paquito', 'Pérez', 'González', 'paquito.perez@example.com', '5555555555', 1);

INSERT INTO Capacitor (Nombre, Primer_apellido, Segundo_apellido, Numero_celular,Correo, Password) 
VALUES ('María', 'López', 'Hernández', '4444444444', 'maria.lopez@example.com', 'mypass123');

INSERT INTO Tema (Descripcion_tema, Nombre_tema) 
VALUES ('Matemáticas básicas', 'Matemáticas');

INSERT INTO Curso (Nombre_curso, Descripcion_curso, Capacitador, Tema) 
VALUES ('Curso de Álgebra', 'Curso introductorio de álgebra', 22024126629, 1);

INSERT INTO Registro_curso (Fecha_inscripcion, Fecha_inicio, Fecha_fin, Estado, Calificaciones, Progreso, Cursante, Curso) 
VALUES ('2024-01-01', '2024-01-02', '2024-02-01', 1, 85.00, 100.00,12024123119, 2024000002);

INSERT INTO Tipo_test (Descripcion) 
VALUES ('Examen final');

INSERT INTO Test (Nombre_test, Descripcion_test, Fecha_limite, Puntaje, Tipo_test, Curso) 
VALUES ('Examen Final', 'Evaluación final del curso', '2024-12-31', 100, 1, 2024000002);



select * from Registro_curso

/*************

   Triggers

*************/

DELIMITER //

CREATE PROCEDURE CalcularAvancePorCurso(
    IN p_Cursante BIGINT, 
    IN p_Curso INT
)
BEGIN
    DECLARE total_preguntas INT;
    DECLARE preguntas_respondidas INT;
    DECLARE porcentaje_avance DECIMAL(5,2);

    -- Obtener el número total de preguntas del curso
    SELECT COUNT(*)
    INTO total_preguntas
    FROM Pregunta P
    JOIN Test T ON P.Test = T.Codigo_test
    WHERE T.Curso = p_Curso;

    -- Contar el número de preguntas respondidas por el cursante
    SELECT COUNT(DISTINCT R.Pregunta)
    INTO preguntas_respondidas
    FROM Respuestas R
    JOIN Pregunta P ON R.Pregunta = P.Num_pregunta
    JOIN Test T ON P.Test = T.Codigo_test
    WHERE T.Curso = p_Curso AND R.Cursante = p_Cursante;

    -- Calcular el porcentaje de avance
    IF total_preguntas > 0 THEN
        SET porcentaje_avance = (preguntas_respondidas / total_preguntas) * 100;
    ELSE
        SET porcentaje_avance = 0;
    END IF;

    -- Actualizar el progreso en Registro_curso
    UPDATE Registro_curso
    SET Progreso = porcentaje_avance
    WHERE Curso = p_Curso AND Cursante = p_Cursante;
END //

DELIMITER ;

DELIMITER //

CREATE PROCEDURE ActualizarAvanceCursante(
    IN p_Cursante BIGINT
)
BEGIN
    DECLARE curso_id INT;
    DECLARE done INT DEFAULT 0;
    DECLARE curso_cursor CURSOR FOR
        SELECT Curso FROM Registro_curso WHERE Cursante = p_Cursante;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    -- Abrir el cursor para recorrer los cursos del cursante
    OPEN curso_cursor;

    curso_loop: LOOP
        FETCH curso_cursor INTO curso_id;

        -- Salir del loop si no hay más cursos
        IF done THEN
            LEAVE curso_loop;
        END IF;

        -- Llamar al procedimiento para calcular el avance del cursante en este curso
        CALL CalcularAvancePorCurso(p_Cursante, curso_id);
    END LOOP;

    -- Cerrar el cursor
    CLOSE curso_cursor;
END //

DELIMITER ;


DELIMITER //

CREATE TRIGGER trg_generate_matricula_cursante
BEFORE INSERT ON Cursante
FOR EACH ROW
BEGIN
    DECLARE current_year CHAR(4);
    DECLARE current_month CHAR(2);
    DECLARE generated_matricula VARCHAR(20);
    
    -- Obtener el año y mes actuales
    SET current_year = DATE_FORMAT(NOW(), '%Y');
    SET current_month = DATE_FORMAT(NOW(), '%m');
    
    -- Generar la matrícula con prefijo 1 (para Cursante)
    SET generated_matricula = CONCAT(
        '1',
        current_year,
        current_month,
        LPAD(FLOOR(RAND() * 10000), 4, '0')
    );
    
    -- Asignar la matrícula generada
    SET NEW.Matricula = generated_matricula;
END //

DELIMITER ;

DELIMITER //

CREATE TRIGGER trg_generate_codigo_capacitor
BEFORE INSERT ON Capacitor
FOR EACH ROW
BEGIN
    DECLARE current_year CHAR(4);
    DECLARE current_month CHAR(2);
    DECLARE generated_codigo_capacitador VARCHAR(20);
    
    -- Obtener el año y mes actuales
    SET current_year = DATE_FORMAT(NOW(), '%Y');
    SET current_month = DATE_FORMAT(NOW(), '%m');
    
    -- Generar el código con prefijo 2 (para Capacitor)
    SET generated_codigo_capacitador = CONCAT(
        '2',
        current_year,
        current_month,
        LPAD(FLOOR(RAND() * 10000), 4, '0')
    );
    
    -- Asignar el código generado
    SET NEW.Codigo_capacitador = generated_codigo_capacitador;
END //

DELIMITER ;

DELIMITER //

CREATE TRIGGER trg_generate_course_code
BEFORE INSERT ON Curso
FOR EACH ROW
BEGIN
    DECLARE last_incremental BIGINT;

    -- Obtener el último código insertado después del prefijo '2024'
    SELECT COALESCE(MAX(CAST(SUBSTRING(Codigo_Curso, 5, 6) AS UNSIGNED)), 0) INTO last_incremental FROM Curso;

    -- Generar el nuevo código: '2024' + 6 dígitos incrementales
    SET NEW.Codigo_Curso = CONCAT('2024', LPAD(last_incremental + 1, 6, '0'));
END //

DELIMITER ;

/****************

Procedimientos almacenados

****************/
DROP PROCEDURE `RegistrarCurso`


DELIMITER //
CREATE PROCEDURE RegistrarCurso (
    IN p_Nombre_Curso VARCHAR(100),
    IN p_Descripcion_Curso TEXT,
    IN p_Fecha_Fin DATE,
    IN p_Estado INT,
    IN p_Capacitador BIGINT,  -- Cambiado de INT a BIGINT
    IN p_Tema INT             -- Nuevo parámetro para el tema del curso
)
BEGIN
    DECLARE lastCursoId VARCHAR(20);  -- Usar VARCHAR para el formato del código

    -- Verificar si ya existe un curso con el mismo nombre
    IF EXISTS (SELECT 1 FROM Curso WHERE Nombre_Curso = p_Nombre_Curso) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El curso ya existe';
    ELSE
        -- Insertar el nuevo curso y obtener el código generado
        INSERT INTO Curso (Nombre_Curso, Descripcion_Curso, Capacitador, Tema)
        VALUES (p_Nombre_Curso, p_Descripcion_Curso, p_Capacitador, p_Tema);

        -- Obtener el último Código_Curso insertado
        SET lastCursoId = (SELECT Codigo_Curso FROM Curso WHERE Nombre_Curso = p_Nombre_Curso ORDER BY Codigo_Curso DESC LIMIT 1);

        -- Verificar si se obtuvo un ID válido
        IF lastCursoId IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Error al obtener el Código del curso recién creado';
        END IF;

        -- Insertar en Registro_curso
        INSERT INTO Registro_curso (Curso, Fecha_inicio, Fecha_fin, Estado)
        VALUES (lastCursoId, CURDATE(), p_Fecha_Fin, p_Estado);
    END IF;
END //
DELIMITER ;


select * from `Curso`

DELIMITER //

DELIMITER //

CREATE PROCEDURE GenerarCertificacion(
    IN cursanteId BIGINT, 
    IN cursoId INT
)
BEGIN
    INSERT INTO Certificacion (Desc_certificacion, Titulo_certificacion, Fecha_expedicion, Cursante, Test)
    SELECT 
        CONCAT('Certificado del curso: ', Curso.Nombre_curso), -- Descripción personalizada
        'Certificado de Finalización',                        -- Título fijo
        CURDATE(),                                            -- Fecha actual
        Cursante.Matricula,                                   -- ID del cursante
        Test.Codigo_test                                      -- Un test del curso
    FROM Curso
    INNER JOIN Registro_curso ON Curso.Codigo_curso = Registro_curso.Curso
    INNER JOIN Cursante ON Registro_curso.Cursante = Cursante.Matricula
    INNER JOIN Test ON Curso.Codigo_curso = Test.Curso
    WHERE Cursante.Matricula = cursanteId 
      AND Curso.Codigo_curso = cursoId
    LIMIT 1;
END//

DELIMITER ;


DELIMITER //

CREATE PROCEDURE VerificarExamen(
    IN cursanteId BIGINT, 
    IN testId INT
)
BEGIN
    DECLARE cursoId INT;
    DECLARE tipoTest VARCHAR(255);
    DECLARE calificacion DECIMAL(5,2);

    -- Verificar el tipo de test y obtener el curso relacionado
    SELECT Tipo_test.Descripcion, Test.Curso
    INTO tipoTest, cursoId
    FROM Test
    INNER JOIN Tipo_test ON Test.Tipo_test = Tipo_test.Codigo_tipotest
    WHERE Test.Codigo_test = testId;

    -- Validar si el test es de tipo "Examen"
    IF tipoTest != 'Examen' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El test no es de tipo "Examen".';
    END IF;

    -- Obtener la calificación del cursante en el curso
    SELECT Calificaciones
    INTO calificacion
    FROM Registro_curso
    WHERE Cursante = cursanteId AND Curso = cursoId;

    -- Validar si la calificación es suficiente
    IF calificacion >= 70 THEN
        -- Llamar al procedimiento GenerarCertificacion
        CALL GenerarCertificacion(cursanteId, cursoId);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La calificación no es suficiente para generar el certificado.';
    END IF;
END//

DELIMITER ;


drop Procedure ActualizarEstadoExamenFinal

use CursosDBN


select * from Registro_curso

drop PROCEDURE GenerarCertificacion

CREATE VIEW Vista_Inscripcion_Cursos AS
SELECT 
    rc.Cursante AS Matricula,
    c.Nombre AS NombreAlumno,
    cr.Nombre_curso AS Nombre_Curso,
    e.Descripcion AS Estado,
    rc.Calificaciones AS Calificacion,
    rc.Progreso AS Progreso
FROM Registro_curso rc
JOIN Cursante c ON rc.Cursante = c.Matricula
JOIN Curso cr ON rc.Curso = cr.Codigo_curso
JOIN Estado e ON rc.Estado = e.ID_estado;



--prueba 

DELIMITER $$

CREATE TRIGGER actualizar_calificacion
AFTER INSERT ON Respuestas
FOR EACH ROW
BEGIN
    DECLARE cursante_matricula BIGINT;
    DECLARE curso_id INT;
    DECLARE puntaje_total DECIMAL(5,2);

    -- Obtener la matrícula del cursante y el curso del test resuelto
    SELECT R.Cursante, T.Curso
    INTO cursante_matricula, curso_id
    FROM Registro_curso R
    JOIN Test T ON T.Codigo_test = NEW.Pregunta
    WHERE R.Curso = T.Curso AND R.Cursante = cursante_matricula
    LIMIT 1;

    -- Calcular el puntaje total obtenido por el cursante en el test
    SELECT SUM(P.Puntaje_respuesta)
    INTO puntaje_total
    FROM Respuestas P
    WHERE P.Pregunta = NEW.Pregunta;

    -- Actualizar la calificación en Registro_curso
    UPDATE Registro_curso
    SET Calificaciones = puntaje_total
    WHERE Cursante = cursante_matricula AND Curso = curso_id;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER trg_set_estado_availed
BEFORE INSERT ON Cursante
FOR EACH ROW
BEGIN
    -- Verificar si existe un estado con descripción "availed"
    DECLARE estado_availed INT;
    SELECT ID_estado INTO estado_availed
    FROM Estado
    WHERE Descripcion = 'availed'
    LIMIT 1;

    -- Si no existe, insertar el estado "availed" en la tabla Estado
    IF estado_availed IS NULL THEN
        INSERT INTO Estado (Descripcion, Progreso_curso)
        VALUES ('availed', 0.00);

        -- Recuperar el ID del nuevo estado insertado
        SET estado_availed = LAST_INSERT_ID();
    END IF;

    -- Asignar el estado "availed" al nuevo cursante
    SET NEW.Estado = estado_availed;
END$$

DELIMITER ;

select * from `Tipo_test`

