-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Mar 27 Mai 2014 à 21:29
-- Version du serveur: 5.5.24-log
-- Version de PHP: 5.4.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `projet_web`
--

--
-- Contenu de la table `groups`
--

INSERT INTO `groups` (`name`) VALUES
('cir1'),
('cir2');

--
-- Contenu de la table `role`
--

INSERT INTO `role` (`role`) VALUES
('student'),
('teacher');

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`username`, `hash`, `firstname`, `lastname`, `mail`, `role`) VALUES
('abbo', '', 'jean', 'beaurepaire', 'abbo@mail.fr', 'student'),
('bogoss', '', 'stephane', 'perreaux', 'bogoss@mail.fr', 'student'),
('jaco', '', 'gilles', 'jacovetti', 'jaco@mail.fr', 'teacher'),
('teacher', '', 'Tea', 'Cher', 'teacher@school.fr', 'teacher'),
('student', '', 'Stu', 'Dent', 'student@school.fr', 'student'),
('studentcir1', '', 'Student', 'CIR1', 'studentcir1@school.fr', 'student'),
('pizza', '', 'theo', 'fundone', 'pizza@mail.fr', 'student'),
('yoyolabricot', '', 'yohann', 'tran', 'yoyo@mail.fr', 'student');

--
-- Contenu de la table `project`
--

INSERT INTO `project` (`id`, `username`, `name`, `enabled`, `due_date`, `target_group`) VALUES
(1, 'teacher', 'Labyrinthe', 1, '2030-05-07 00:00:00', 'cir1'),
(2, 'jaco', 'Démineur', 0, '2030-05-19 05:50:00', 'cir1'),
(3, 'teacher', 'Tests des tests unitaires', 0, '2030-05-19 05:50:00', 'cir2');

--
-- Contenu de la table `test`
--

/*INSERT INTO `test` (`project_id`, `name`, `description`) VALUES
(1, 'testDroite', 'testDroite testDroite testDroite'),
(1, 'testGauche', 'testGauche testGauche testGauche');*/ /* Maybe we can have fixtures + samples files ? */

--
-- Contenu de la table `subtest`
--

/*INSERT INTO `subtest` (`project_id`, `test_name`, `name`, `weight`, `kind`) VALUES
(1, 'TestDroite', 'droitedroite', 5, 'AssertBidule'),
(1, 'TestDroite', 'droitegauche', 2, 'AssertMachin');*/

--
-- Contenu de la table `users_groups`
--

INSERT INTO `users_groups` (`group_name`, `username`) VALUES
('cir2', 'abbo'),
('cir2', 'bogoss'),
('cir1', 'student'),
('cir2', 'student'),
('cir1', 'studentcir1'),
('cir2', 'pizza'),
('cir2', 'yoyolabricot');

--
-- Contenu de la table `users_test`
--

/*INSERT INTO `projet_web`.`users_test` (`project_id`, `test_name`, `subtest_name`, `username`, `status`, `errors`) VALUES 
('1', 'testDroite', 'droitedroite', 'pizza', '1', NULL), 
('1', 'testDroite', 'droitegauche', 'pizza', '0', 'Erreur pizza, trop de pepperonis !');*/

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
