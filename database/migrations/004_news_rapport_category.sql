-- =====================================================================
-- ATLEX - Sport — Migration 004 : catégorie « Rapports d'activité »
-- =====================================================================
-- Ajoute la valeur 'rapport' à l'enum des catégories d'actualités
-- (rapports de salons / d'activités auxquels l'association a participé).

ALTER TABLE news_articles
  MODIFY category ENUM('resultat','recrutement','evenement','partenariat','general','rapport')
  DEFAULT 'general';
