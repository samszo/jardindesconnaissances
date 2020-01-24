INSERT INTO gen_doublons (title, type, lexTermElement,lexTermElement_e,description)
SELECT 
            concat(p.type,p.num)
            , p.type
            , p.lib, p.lib_eli, concat(p.type,p.num)
        FROM
            gen_pronoms p