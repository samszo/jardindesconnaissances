SELECT 
    COUNT(*) nb, 
    COUNT(DISTINCT d.title) nbTitle,
    d.title,
    GROUP_CONCAT(DISTINCT d.refC) refC,
    GROUP_CONCAT(DISTINCT d.refL) refL,
    GROUP_CONCAT(DISTINCT d.type_concept) arrTypeConcept,
    GROUP_CONCAT(DISTINCT d.type_lien) arrTypeLien,
    d.description,
    d.lexTermElement, 
    MAX(d.lexTermElement_e) lexTermElement_e, 
    MAX(d.grammaticalGender) grammaticalGender, 
    MAX(d.pluralNumberForm) pluralNumberForm, 
    MAX(d.pluralNumberForm_f) pluralNumberForm_f, 
    MAX(d.pluralNumberForm_fe) pluralNumberForm_fe, 
    MAX(d.pluralNumberForm_m) pluralNumberForm_m, 
    MAX(d.pluralNumberForm_me) pluralNumberForm_me, 
    MAX(d.singularNumberForm) singularNumberForm, 
    MAX(d.singularNumberForm_f) singularNumberForm_f, 
    MAX(d.singularNumberForm_fe) singularNumberForm_fe, 
    MAX(d.singularNumberForm_m) singularNumberForm_m, 
    MAX(d.singularNumberForm_me) singularNumberForm_me, 
    MAX(d.aspect) aspect
FROM
    gen_doublons d
 WHERE d.title <> '' AND d.type_concept <> ''  -- AND d.type_lien = 'determinant' 
GROUP BY d.title, d.description, d.lexTermElement
ORDER BY  d.title, arrTypeConcept, arrTypeLien;  -- d.title; -- grammaticalGender --  aspect DESC -- nbTitle DESC;

--  truncate table gen_doublons;