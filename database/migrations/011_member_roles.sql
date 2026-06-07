-- Migration 011: Add role column to members table
ALTER TABLE members
  ADD COLUMN role ENUM(
    'benevole',
    'bureau',
    'president',
    'secretaire_general',
    'tresorier',
    'responsable_technique',
    'autre'
  ) NULL AFTER discipline,
  MODIFY COLUMN discipline ENUM('football','basketball','handball','arts_martiaux') NULL;

-- Set default role for existing members
UPDATE members SET role = 'autre' WHERE role IS NULL;
