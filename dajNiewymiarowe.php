<?php
		header('Content-type: text/plain');
		$conn = pg_connect("host=127.0.0.1 port=5432 dbname=ROD_Gliwice user=postgres password=postgres");
		if (!$conn) {
				echo "{success: false, message: 'B��d pod��czenia do bazy'}";
				exit;
		}			
		$result = pg_query($conn, 
				"SELECT 
						d.numer AS numer, 
						d.powierzchnia AS powierzchnia, 
						o.nazwa || '(' || o.parcela || ')' AS ogrod, 
						m.nazwa AS miasto, l.nazwa AS delegatura, 
						ST_AsGeoJSON(ST_Transform(d.the_geom, 900913)) AS geom 
				FROM 
						dzialki d, 
						ogrody o, 
						miasta m, 
						delegatury l 
				WHERE 
						d.powierzchnia > 500 AND 
						position('al' in lower(d.numer)) = 0 AND 
						d.id_ogrodu = o.gid AND 
						o.id_miasta = m.gid AND 
						o.id_deleg = l.gid");
		while ($row = pg_fetch_row($result)) {
				$arr[]=array(
						"type" => "Feature",
						"properties" 	=> array(
								"numer" 				=> $row[0],
								"powierzchnia" 	=> $row[1],
								"ogrod" 				=> $row[2],
								"miasto" 				=> $row[3],
								"delegatura" 		=> $row[4]
						),
						"geometry" 		=> $row[5]						
				);
				//echo pg_client_encoding($conn);
		}
		$json = json_encode($arr);
		$json = str_replace("\"{", "{", $json);
		$json = str_replace("}\"", "}", $json);
		$json = stripslashes($json);
		
		echo '{"type":"FeatureCollection","features":'.$json.'}';
?>