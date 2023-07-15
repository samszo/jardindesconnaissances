SELECT 
    count(distinct g.geo_id) nb,
	1 niv,
    count(distinct g.geo_id)*1 complexite

FROM
    flux_geo g
-- WHERE
--    g.geo_id = 1
