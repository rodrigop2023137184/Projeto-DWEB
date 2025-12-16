-- --------------------------------------------------------
-- Anfitrião:                    127.0.0.1
-- Versão do servidor:           8.4.3 - MySQL Community Server - GPL
-- SO do servidor:               Win64
-- HeidiSQL Versão:              12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- A despejar estrutura da base de dados para crcdatabase
CREATE DATABASE IF NOT EXISTS `crcdatabase` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `crcdatabase`;

-- A despejar estrutura para tabela crcdatabase.carrinho
CREATE TABLE IF NOT EXISTS `carrinho` (
  `id_carrinho` int NOT NULL AUTO_INCREMENT,
  `id_utilizador` int NOT NULL,
  `id_produto` int NOT NULL,
  `quantidade` int DEFAULT '1',
  `tamanho_escolhido` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `data_adicao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_carrinho`),
  UNIQUE KEY `unique_item_carrinho` (`id_utilizador`,`id_produto`,`tamanho_escolhido`),
  KEY `idx_utilizador` (`id_utilizador`),
  KEY `idx_produto` (`id_produto`),
  KEY `idx_data_adicao` (`data_adicao`),
  CONSTRAINT `carrinho_ibfk_1` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizador` (`id_utilizador`) ON DELETE CASCADE,
  CONSTRAINT `carrinho_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela crcdatabase.carrinho: ~2 rows (aproximadamente)
INSERT INTO `carrinho` (`id_carrinho`, `id_utilizador`, `id_produto`, `quantidade`, `tamanho_escolhido`, `preco_unitario`, `data_adicao`, `data_atualizacao`) VALUES
	(3, 1, 7, 1, '', 18.00, '2025-12-08 21:03:34', '2025-12-08 21:03:49'),
	(6, 3, 4, 1, 'M', 28.00, '2025-12-16 18:24:08', '2025-12-16 18:24:08');

-- A despejar estrutura para tabela crcdatabase.encomenda
CREATE TABLE IF NOT EXISTS `encomenda` (
  `id_encomenda` int NOT NULL AUTO_INCREMENT,
  `id_utilizador` int NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `taxa_envio` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `nome_destinatario` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `morada_envio` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_postal` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metodo_pagamento` enum('mbway','multibanco','cartao','transferencia') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_pagamento` enum('pendente','pago','falhado','reembolsado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `referencia_pagamento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `estado` enum('pendente','confirmada','a_preparar','enviada','entregue','cancelada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `numero_rastreio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas_cliente` text COLLATE utf8mb4_unicode_ci,
  `notas_internas` text COLLATE utf8mb4_unicode_ci,
  `data_encomenda` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_encomenda`),
  KEY `idx_utilizador` (`id_utilizador`),
  KEY `idx_estado` (`estado`),
  KEY `idx_data_encomenda` (`data_encomenda`),
  CONSTRAINT `encomenda_ibfk_1` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizador` (`id_utilizador`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela crcdatabase.encomenda: ~3 rows (aproximadamente)
INSERT INTO `encomenda` (`id_encomenda`, `id_utilizador`, `subtotal`, `taxa_envio`, `total`, `nome_destinatario`, `morada_envio`, `codigo_postal`, `cidade`, `telefone`, `metodo_pagamento`, `estado_pagamento`, `referencia_pagamento`, `data_pagamento`, `estado`, `numero_rastreio`, `notas_cliente`, `notas_internas`, `data_encomenda`, `data_atualizacao`) VALUES
	(1, 1, 25.00, 5.00, 30.00, 'Rodrigo Pereira', 'Rua do pinhal', '3120-232', 'Coimbra', '988455676', 'mbway', 'pendente', 'MBWAY-1', NULL, 'pendente', NULL, '', NULL, '2025-12-08 21:01:44', '2025-12-08 21:01:44'),
	(2, 2, 35.00, 5.00, 40.00, 'Paulo Jorge', 'Rua 25 de abril', '3180-838', 'Coimbra', '988455676', 'multibanco', 'pendente', 'Entidade: 12345 | Referência: 000000002', NULL, 'pendente', NULL, '', NULL, '2025-12-12 19:37:25', '2025-12-12 19:37:25'),
	(3, 2, 40.00, 5.00, 45.00, 'Paulo Jorge', 'Rua 25 de abril', '3180-838', 'Coimbra', '988455676', 'multibanco', 'pendente', 'Entidade: 12345 | Referência: 000000003', NULL, 'pendente', NULL, '', NULL, '2025-12-15 15:07:40', '2025-12-15 15:07:40');

-- A despejar estrutura para tabela crcdatabase.encomenda_item
CREATE TABLE IF NOT EXISTS `encomenda_item` (
  `id_item` int NOT NULL AUTO_INCREMENT,
  `id_encomenda` int NOT NULL,
  `id_produto` int NOT NULL,
  `nome_produto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamanho` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantidade` int NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_item`),
  KEY `idx_encomenda` (`id_encomenda`),
  KEY `idx_produto` (`id_produto`),
  CONSTRAINT `encomenda_item_ibfk_1` FOREIGN KEY (`id_encomenda`) REFERENCES `encomenda` (`id_encomenda`) ON DELETE CASCADE,
  CONSTRAINT `encomenda_item_ibfk_2` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela crcdatabase.encomenda_item: ~3 rows (aproximadamente)
INSERT INTO `encomenda_item` (`id_item`, `id_encomenda`, `id_produto`, `nome_produto`, `tamanho`, `quantidade`, `preco_unitario`, `subtotal`) VALUES
	(1, 1, 3, 'T-shirt CRC multicor', 'S', 1, 25.00, 25.00),
	(2, 2, 5, 'Calças CRC', 'M', 1, 35.00, 35.00),
	(3, 3, 8, 'Hoodie CRC Oficial', 'S', 1, 40.00, 40.00);

-- A despejar estrutura para tabela crcdatabase.evento
CREATE TABLE IF NOT EXISTS `evento` (
  `id_evento` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitulo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao_curta` text COLLATE utf8mb4_unicode_ci,
  `descricao_completa` text COLLATE utf8mb4_unicode_ci,
  `data_evento` date NOT NULL,
  `hora_evento` time NOT NULL,
  `duracao_estimada` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `local_nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `local_endereco` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distancias_disponiveis` json DEFAULT NULL,
  `detalhes_percurso` json DEFAULT NULL,
  `vagas_totais` int DEFAULT NULL,
  `vagas_ocupadas` int DEFAULT '0',
  `preco` decimal(10,2) NOT NULL,
  `categoria` enum('corrida','trail','maratona','caminhada') COLLATE utf8mb4_unicode_ci DEFAULT 'corrida',
  `itens_incluidos` json DEFAULT NULL,
  `tem_transporte` tinyint(1) DEFAULT '0',
  `status` enum('ativo','cancelado','concluido') COLLATE utf8mb4_unicode_ci DEFAULT 'ativo',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_evento`),
  KEY `idx_data_evento` (`data_evento`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela crcdatabase.evento: ~6 rows (aproximadamente)
INSERT INTO `evento` (`id_evento`, `titulo`, `subtitulo`, `descricao_curta`, `descricao_completa`, `data_evento`, `hora_evento`, `duracao_estimada`, `local_nome`, `local_endereco`, `imagem`, `distancias_disponiveis`, `detalhes_percurso`, `vagas_totais`, `vagas_ocupadas`, `preco`, `categoria`, `itens_incluidos`, `tem_transporte`, `status`, `data_criacao`) VALUES
	(1, 'Coimbra Neon Run', '5 KM · 10 KM', 'A Corrida Noturna de Coimbra regressa para mais uma edição emocionante!', 'Este evento oferece dois percursos distintos: 5km para iniciantes e famílias, e 10km para os mais experientes. Ambos os percursos atravessam os pontos mais icónicos de Coimbra, proporcionando vistas deslumbrantes da cidade iluminada. Todos os participantes receberão um kit de corredor com t-shirt técnica oficial do evento, dorsal com chip de cronometragem, e diversos brindes de patrocinadores.', '2025-12-28', '20:00:00', '2-3 horas', 'Parque Verde do Mondego', 'Parque Dr. Manuel Braga, 3000 Coimbra', 'imgs/AV-neonrun-2016-2299SAM_9014.jpg', '["5km", "10km"]', '{"5km": [{"nome": "Partida", "local": "Parque Verde do Mondego"}, {"nome": "Ponto 1", "local": "Ponte Pedro e Inês"}, {"nome": "Ponto 2", "local": "Margem do Mondego"}, {"nome": "Chegada", "local": "Parque Verde do Mondego"}], "10km": [{"nome": "Partida", "local": "Parque Verde do Mondego"}, {"nome": "Ponto 1", "local": "Centro Histórico"}, {"nome": "Ponto 2", "local": "Universidade de Coimbra"}, {"nome": "Ponto 3", "local": "Baixa da Cidade"}, {"nome": "Chegada", "local": "Parque Verde do Mondego"}]}', 500, 1, 15.00, 'corrida', '["T-shirt técnica oficial do evento", "Dorsal com chip de cronometragem", "Medalha de participação", "Seguro de participante", "Postos de hidratação", "Lanche final", "Transporte de regresso", "Apoio médico durante o evento"]', 1, 'ativo', '2025-11-26 19:30:39'),
	(2, 'Meia Maratona de Coimbra', '21 KM', 'O maior evento de atletismo da região! Percurso certificado pela IAAF.', 'A Meia Maratona de Coimbra é um evento desportivo de referência nacional que atrai milhares de corredores. Com um percurso plano e rápido, certificado internacionalmente, é ideal tanto para quem procura o seu melhor tempo como para quem quer viver uma experiência única na cidade dos estudantes.', '2026-03-22', '09:00:00', '2-4 horas', 'Estádio Cidade de Coimbra', 'Av. Urbano Duarte, 3030 Coimbra', 'imgs/run_aesthetic_2.jpg', '["21km"]', '{"21km": [{"nome": "Partida", "local": "Estádio Cidade de Coimbra"}, {"nome": "5km", "local": "Solum"}, {"nome": "10km", "local": "Ponte de Santa Clara"}, {"nome": "15km", "local": "Universidade"}, {"nome": "20km", "local": "Baixa"}, {"nome": "Chegada", "local": "Estádio Cidade de Coimbra"}]}', 1000, 3, 25.00, 'maratona', '["T-shirt técnica premium", "Dorsal com chip", "Medalha de finisher", "Diploma digital", "Seguro desportivo", "5 postos de hidratação", "Fisioterapia pós-corrida", "Almoço final", "Transporte de bagagens"]', 1, 'ativo', '2025-11-27 14:59:36'),
	(3, 'Trail Serra da Lousã', '15 KM · 30 KM', 'Aventura extrema na natureza! Percursos técnicos de trail running com paisagens deslumbrantes.', 'O Trail Serra da Lousã é o evento perfeito para os amantes de trail running. Com dois percursos desafiantes, atravessa as aldeias do xisto, trilhos de montanha e oferece vistas panorâmicas únicas. O percurso de 30km inclui 1500m de desnível acumulado positivo.', '2026-05-10', '08:00:00', '3-6 horas', 'Lousã - Centro da Vila', 'Praça da República, Lousã', 'imgs/run_aesthetic_3.jpg', '["15km", "30km"]', '{"15km": [{"nome": "Partida", "local": "Lousã Centro"}, {"nome": "Subida", "local": "Aldeia do Talasnal"}, {"nome": "Miradouro", "local": "Alto do Trevim"}, {"nome": "Chegada", "local": "Lousã Centro"}], "30km": [{"nome": "Partida", "local": "Lousã Centro"}, {"nome": "CP1", "local": "Candal"}, {"nome": "CP2", "local": "Talasnal"}, {"nome": "CP3", "local": "Aigra Nova"}, {"nome": "CP4", "local": "Comareira"}, {"nome": "Chegada", "local": "Lousã Centro"}]}', 300, 0, 20.00, 'trail', '["T-shirt técnica trail", "Dorsal", "Medalha de finisher", "Buff exclusivo", "Seguro desportivo", "Postos de avitualhamento", "Bastões de trail (opcional)", "Refeição completa", "Transporte de partida"]', 1, 'ativo', '2025-11-27 14:59:36'),
	(4, 'Corrida Solidária CRC', '3 KM · 6 KM', 'Corre por uma causa! Evento solidário com toda a receita revertida para instituições de apoio social.', 'A Corrida Solidária do Coimbra Running Club é mais do que uma corrida - é um gesto de solidariedade. Aberta a toda a família, com percursos de 3km (caminhada permitida) e 6km, este evento pretende angariação de fundos para instituições locais.', '2026-04-05', '10:00:00', '1-2 horas', 'Parque Verde do Mondego', 'Parque Dr. Manuel Braga, 3000 Coimbra', 'imgs/run_aesthetic_4.jpg', '["3km", "6km"]', '{"3km": [{"nome": "Partida", "local": "Parque Verde"}, {"nome": "Volta", "local": "Margem do Mondego"}, {"nome": "Chegada", "local": "Parque Verde"}], "6km": [{"nome": "Partida", "local": "Parque Verde"}, {"nome": "CP1", "local": "Ponte Pedro e Inês"}, {"nome": "CP2", "local": "Portugal dos Pequenitos"}, {"nome": "Chegada", "local": "Parque Verde"}]}', 800, 0, 8.00, 'corrida', '["T-shirt solidária", "Dorsal", "Medalha de participação", "Seguro", "Hidratação", "Lanche", "Entretenimento infantil", "Sorteio de prémios"]', 0, 'ativo', '2025-11-27 14:59:36'),
	(5, 'Maratona de Coimbra', '42 KM', 'O desafio definitivo! A primeira maratona oficial de Coimbra, com percurso homologado.', 'Estreia histórica da Maratona de Coimbra! São 42.195km de desafio puro através da cidade e arredores. Percurso homologado pela World Athletics, com cronometragem eletrónica certificada e todo o apoio necessário para atingir o seu objetivo.', '2026-10-18', '08:00:00', '3-7 horas', 'Estádio Cidade de Coimbra', 'Av. Urbano Duarte, 3030 Coimbra', 'imgs/run_aesthetic_5.jpg', '["42km"]', '{"42km": [{"nome": "Partida", "local": "Estádio Cidade"}, {"nome": "10km", "local": "Taveiro"}, {"nome": "21km", "local": "Eiras"}, {"nome": "30km", "local": "Universidade"}, {"nome": "35km", "local": "Centro Histórico"}, {"nome": "40km", "local": "Calhabé"}, {"nome": "Chegada", "local": "Estádio Cidade"}]}', 600, 1, 40.00, 'maratona', '["T-shirt técnica premium", "Dorsal com chip profissional", "Medalha finisher exclusiva", "Diploma certificado", "Manta térmica", "Seguro completo", "8 postos de hidratação", "Géis energéticos", "Massagem desportiva", "Refeição completa", "Fotos profissionais"]', 1, 'ativo', '2025-11-27 14:59:36'),
	(6, 'Night Run Coimbra', '7 KM', 'Corrida noturna pela Baixa! Música, luzes e energia numa corrida urbana única que termina com festa.', 'A Night Run Coimbra é o evento mais animado do calendário! Uma corrida noturna de 7km pela Baixa da cidade, com DJ, luzes, animação de rua e muita energia. Não é uma corrida competitiva - é uma celebração do desporto e da diversão!', '2026-07-25', '21:30:00', '1-2 horas', 'Praça 8 de Maio', 'Baixa de Coimbra, 3000 Coimbra', 'imgs/run_aesthetic_6.jpg', '["7km"]', '{"7km": [{"nome": "Partida", "local": "Praça 8 de Maio"}, {"nome": "Ferreira Borges", "local": "Rua Ferreira Borges"}, {"nome": "República", "local": "Praça da República"}, {"nome": "Alta", "local": "Alta de Coimbra"}, {"nome": "Parque", "local": "Parque Verde"}, {"nome": "Chegada/Festa", "local": "Parque Verde"}]}', 400, 1, 18.00, 'corrida', '["T-shirt refletora", "Pulseira LED", "Dorsal", "Seguro", "Bebidas", "Acesso à after-party", "DJ ao vivo", "Photobooth", "Surpresas no percurso"]', 0, 'ativo', '2025-11-27 14:59:36');

-- A despejar estrutura para tabela crcdatabase.inscricao_evento
CREATE TABLE IF NOT EXISTS `inscricao_evento` (
  `id_inscricao` int NOT NULL AUTO_INCREMENT,
  `id_evento` int NOT NULL,
  `id_utilizador` int NOT NULL,
  `distancia_escolhida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamanho_tshirt` enum('XS','S','M','L','XL','XXL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_pago` decimal(10,2) NOT NULL,
  `metodo_pagamento` enum('mbway','multibanco','cartao','transferencia','pendente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `estado_pagamento` enum('pendente','pago','cancelado','reembolsado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `referencia_pagamento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `numero_dorsal` int DEFAULT NULL,
  `status` enum('ativa','cancelada','compareceu','nao_compareceu') COLLATE utf8mb4_unicode_ci DEFAULT 'ativa',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `necessidades_especiais` text COLLATE utf8mb4_unicode_ci,
  `data_inscricao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_cancelamento` datetime DEFAULT NULL,
  PRIMARY KEY (`id_inscricao`),
  UNIQUE KEY `unique_inscricao` (`id_evento`,`id_utilizador`),
  KEY `idx_evento` (`id_evento`),
  KEY `idx_utilizador` (`id_utilizador`),
  KEY `idx_estado_pagamento` (`estado_pagamento`),
  KEY `idx_numero_dorsal` (`numero_dorsal`),
  CONSTRAINT `inscricao_evento_ibfk_1` FOREIGN KEY (`id_evento`) REFERENCES `evento` (`id_evento`) ON DELETE CASCADE,
  CONSTRAINT `inscricao_evento_ibfk_2` FOREIGN KEY (`id_utilizador`) REFERENCES `utilizador` (`id_utilizador`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela crcdatabase.inscricao_evento: ~6 rows (aproximadamente)
INSERT INTO `inscricao_evento` (`id_inscricao`, `id_evento`, `id_utilizador`, `distancia_escolhida`, `tamanho_tshirt`, `valor_pago`, `metodo_pagamento`, `estado_pagamento`, `referencia_pagamento`, `data_pagamento`, `numero_dorsal`, `status`, `observacoes`, `necessidades_especiais`, `data_inscricao`, `data_cancelamento`) VALUES
	(1, 2, 2, '21km', 'M', 25.00, 'pendente', 'pendente', NULL, NULL, 1, 'ativa', NULL, NULL, '2025-12-02 19:09:53', NULL),
	(2, 1, 1, '5km', 'M', 15.00, 'pendente', 'pendente', NULL, NULL, 1, 'ativa', NULL, NULL, '2025-12-03 19:01:35', NULL),
	(3, 6, 1, '7km', 'M', 18.00, 'pendente', 'pendente', NULL, NULL, 1, 'ativa', NULL, NULL, '2025-12-03 19:01:59', NULL),
	(4, 2, 1, '21km', 'M', 25.00, 'pendente', 'pendente', NULL, NULL, 2, 'ativa', NULL, NULL, '2025-12-03 19:20:03', NULL),
	(5, 5, 1, '42km', 'M', 40.00, 'pendente', 'pendente', NULL, NULL, 1, 'ativa', NULL, NULL, '2025-12-05 20:16:00', NULL),
	(6, 2, 3, '21km', 'M', 25.00, 'pendente', 'pendente', NULL, NULL, 3, 'ativa', NULL, NULL, '2025-12-15 14:57:13', NULL);

-- A despejar estrutura para tabela crcdatabase.produto
CREATE TABLE IF NOT EXISTS `produto` (
  `id_produto` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `categoria` enum('roupa','acessorios','equipamento','outros') COLLATE utf8mb4_unicode_ci DEFAULT 'roupa',
  `preco` decimal(10,2) NOT NULL,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock_total` int DEFAULT '0',
  `tem_tamanhos` tinyint(1) DEFAULT '0',
  `tipo_tamanho` enum('roupa','calcado','unico','numerico') COLLATE utf8mb4_unicode_ci DEFAULT 'unico',
  `material` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `peso` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('ativo','inativo','esgotado') COLLATE utf8mb4_unicode_ci DEFAULT 'ativo',
  `visivel` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_produto`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_status` (`status`),
  KEY `idx_preco` (`preco`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela crcdatabase.produto: ~8 rows (aproximadamente)
INSERT INTO `produto` (`id_produto`, `nome`, `descricao`, `categoria`, `preco`, `imagem`, `stock_total`, `tem_tamanhos`, `tipo_tamanho`, `material`, `peso`, `slug`, `status`, `visivel`, `data_criacao`, `data_atualizacao`) VALUES
	(1, 'T-shirt CRC Oficial Preta', 'T-shirt técnica oficial do Coimbra Running Club. Tecido respirável e de secagem rápida, perfeita para treinos e corridas. Design moderno com logo do clube estampado.', 'roupa', 25.00, 'imgs/vecteezy_professional-white-blank-tshirt-ai-generated_34434822.jpg', 80, 1, 'roupa', 'Poliéster técnico 100%', '150g', 'tshirt-crc-oficial-preta', 'ativo', 1, '2025-12-04 15:09:47', '2025-12-16 15:08:53'),
	(2, 'T-shirt CRC Oficial Branca', 'T-shirt técnica oficial do Coimbra Running Club. Tecido respirável e de secagem rápida, perfeita para treinos e corridas. Design moderno com logo do clube estampado.', 'roupa', 25.00, 'imgs/vecteezy_3d-render-blank-t-shirt-ai-generated_34434757.jpg', 80, 1, 'roupa', 'Poliéster técnico 100%', '150g', 'tshirt-crc-oficial-branca', 'ativo', 1, '2025-12-04 15:09:47', '2025-12-04 15:09:47'),
	(3, 'T-shirt CRC multicor', 'T-shirt técnica oficial do Coimbra Running Club. Tecido respirável e de secagem rápida, perfeita para treinos e corridas. Design moderno com logo do clube estampado.', 'roupa', 25.00, 'imgs/vecteezy_ai-generated-chocolate-chip-cookies-on-orange-background_34991657.jpg', 79, 1, 'roupa', 'Poliéster técnico 100%', '150g', 'tshirt-crc-multicor', 'ativo', 1, '2025-12-04 15:09:47', '2025-12-08 21:01:44'),
	(4, 'Calções de Corrida CRC', 'Calções leves e confortáveis com bolso interior para chaves. Cintura elástica ajustável e costuras planas para evitar irritações.', 'roupa', 28.00, 'imgs/calcoes_com_logo.png', 60, 1, 'roupa', 'Poliéster e Elastano', '120g', 'calcoes-de-corrida-crc', 'ativo', 1, '2025-12-04 15:09:47', '2025-12-04 15:09:47'),
	(5, 'Calças CRC', 'Calças leves e confortáveis com bolsos interiores para chaves. Cintura elástica ajustável e costuras planas para evitar irritações.', 'roupa', 35.00, 'imgs/calcas_com_logo.png', 59, 1, 'roupa', 'Poliéster e Elastano', '220g', 'calcas-crc', 'ativo', 1, '2025-12-04 15:09:47', '2025-12-12 19:37:25'),
	(6, 'Meias Técnicas CRC', 'Meias de corrida com suporte do arco plantar e zona acolchoada. Tecnologia anti-bolhas e ventilação na zona superior do pé.', 'acessorios', 12.00, 'imgs/MeiasCRC.png', 120, 1, 'calcado', 'Algodão 75% / Elastano 25%', '50g', 'meias-tecnicas-crc', 'ativo', 1, '2025-12-04 15:09:47', '2025-12-04 15:09:47'),
	(7, 'Garrafa Térmica CRC 750ml', 'Garrafa metálica de parede dupla que mantém líquidos frios por 24h e quentes por 12h. Logo CRC gravado. Livre de BPA.', 'acessorios', 18.00, 'imgs/GarrafaCRC2.png', 2, 0, NULL, 'Aço inoxidável', '750ml', 'garrafa-termica-crc-750ml', 'ativo', 1, '2025-12-04 15:09:47', '2025-12-16 15:11:20'),
	(8, 'Hoodie CRC Oficial', 'Hoodie oficial do Coimbra Running Club. Tecido confortável e quente, perfeito para os dias mais frios. Design moderno com logo do clube estampado.', 'roupa', 40.00, 'imgs/hoodie_com_logo.png', 79, 1, 'roupa', 'Algodão 100%', '220g', 'hoodie-crc-oficial', 'ativo', 1, '2025-12-04 15:09:47', '2025-12-15 15:07:40');

-- A despejar estrutura para tabela crcdatabase.utilizador
CREATE TABLE IF NOT EXISTS `utilizador` (
  `id_utilizador` int NOT NULL AUTO_INCREMENT,
  `primeiro_nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ultimo_nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date NOT NULL,
  `genero` enum('masculino','feminino','outro','prefiro-nao-dizer') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_registo` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_ultima_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ativo` tinyint(1) DEFAULT '1',
  `tipo` enum('utilizador','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'utilizador',
  `email_verificado` tinyint(1) DEFAULT '0',
  `token_verificacao` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_recuperacao_password` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_expiracao_token` datetime DEFAULT NULL,
  `tentativas_login` int DEFAULT '0',
  `bloqueado_ate` datetime DEFAULT NULL,
  PRIMARY KEY (`id_utilizador`) USING BTREE,
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_data_registo` (`data_registo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A despejar dados para tabela crcdatabase.utilizador: ~3 rows (aproximadamente)
INSERT INTO `utilizador` (`id_utilizador`, `primeiro_nome`, `ultimo_nome`, `email`, `telefone`, `data_nascimento`, `genero`, `password_hash`, `data_registo`, `data_ultima_atualizacao`, `ativo`, `tipo`, `email_verificado`, `token_verificacao`, `token_recuperacao_password`, `data_expiracao_token`, `tentativas_login`, `bloqueado_ate`) VALUES
	(1, 'Rodrigo', 'Pereira', 'a2023137184@alumni.iscac.pt', '988455676', '2025-11-05', 'feminino', '$2y$10$0XyUuRMxTsejDsJOkU.toujQunC4uGk7IYpJqxK0qXRnucYa1tVii', '2025-11-22 20:40:25', '2025-12-14 20:43:25', 0, 'utilizador', 0, '3566fa3d846b093d42a468732fd62a0c170412745a666107c84c0cffcd97b1b89cec449071c8bd1fff27aec9911ea8a08c71', NULL, NULL, 0, NULL),
	(2, 'Paulo', 'Jorge', 'paulojorge@gmail.com', '988455676', '2025-11-20', 'masculino', '$2y$10$PIUdCKkCrBurz/p9zPBc2.SBUBJD2wY8bW8gucEopY.SeLLPY.ehO', '2025-11-22 21:46:06', '2025-12-16 15:06:46', 1, 'utilizador', 0, '1e0a48f76fd424ce9aae9f799e9a7dc0ea0aaa830496e37b114db9decf8bd512aed636b2f3dacebde2111cdf193349be8e5b', NULL, NULL, 0, NULL),
	(3, 'Admin', 'Sistema', 'admin@crc.pt', NULL, '2025-12-10', NULL, '$2y$10$61GmsR9EDn79zrNFzHzY.eJI/SdmI8J5i3zW604FHtHTTLY5pUI7q', '2025-12-10 19:56:10', '2025-12-16 18:47:49', 1, 'admin', 1, NULL, NULL, NULL, 0, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
