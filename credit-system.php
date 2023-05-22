<?php
/*

Plugin Name: User Credits
Description: A plugin that adds a credit system for users in WordPress.
Version: 1.0
Author: CodeDealer
Author URI: https://api.whatsapp.com/send/?phone=584125390872

*/

//Creacion de base de datosalcala_de_guadaira

register_activation_hook( __FILE__, 'my_plugin_install' );

function my_plugin_install() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'users';

    $wpdb->query("ALTER TABLE $table_name ADD credits INT NOT NULL DEFAULT 0");



}
function mi_plugin_enqueue_styles() {
    // Genera la URL del archivo CSS en la carpeta del plugin
    $mi_plugin_css_url = plugin_dir_url(__FILE__) . 'style.css';

    // Encola el archivo CSS
    wp_enqueue_style('style', $mi_plugin_css_url);
}
add_action('wp_enqueue_scripts', 'mi_plugin_enqueue_styles');
function destacados_create_category() {

    // Crear una nueva categoría

    $cat_name = 'Destacados';

    $cat_desc = 'Categoría para los anuncios destacados';

    $cat_id = wp_insert_term($cat_name, 'category', array('description' => $cat_desc, 'parent' => 0));

}

register_activation_hook(__FILE__, 'destacados_create_category');

// Función para borrar la categoría

function destacados_create_page() {

    // Crear una nueva página

    $destacados_page = array(

        'post_title'    => 'Anuncios destacados',

        'post_content'  => '[destacados_form]',

        'post_status'   => 'publish',

        'post_author'   => 1,

        'post_type'     => 'page'

    );

    wp_insert_post( $destacados_page );

}



// Llamar la función cuando se active el plugin

register_activation_hook( __FILE__, 'destacados_create_page' );

function destacados_delete_page() {

    // Obtener la página

    $page = get_page_by_title('Anuncios destacados');



    // Si la página existe, borrarla

    if ($page) {

        wp_delete_post($page->ID, true);

    }

}



// Llamar la función cuando se desactive el plugin

register_deactivation_hook(__FILE__, 'destacados_delete_page');



function destacadoss_delete_page() {

    // Obtener la página

    $page = get_page_by_title('Destacados');



    // Si la página existe, borrarla

    if ($page) {

        wp_delete_post($page->ID, true);

    }

}



// Llamar la función cuando se desactive el plugin

register_deactivation_hook(__FILE__, 'destacadoss_delete_page');



register_activation_hook(__FILE__, 'crear_tabla_destacados');



function crear_tabla_destacados() {

global $wpdb;



$tabla = $wpdb->prefix . "destacados";

$charset_collate = $wpdb->get_charset_collate();



$sql = "CREATE TABLE $tabla (

  id mediumint(9) NOT NULL AUTO_INCREMENT,

  nombre text NOT NULL,

  whatsapp text NOT NULL,

  numero_contacto text NOT NULL,

  edad text NOT NULL,

  descripcion text NOT NULL,

  ubicacion text NOT NULL,

  imagenes text NOT NULL,

  frecuencia text NOT NULL,

  posicion text NOT NULL,

  url text NOT NULL,

  peso text NOT NULL,

  altura text NOT NULL,

  disponibilidad text NOT NULL,

              piel text NOT NULL,

              largocabello text NOT NULL,

              colorcabello text NOT NULL,

              tamañopecho text NOT NULL,

              complexion text NOT NULL,

              fumadora text NOT NULL,

              tarifas text NOT NULL,

              moneda text NOT NULL,

              pechooperado text NOT NULL,

              orientacion text NOT NULL,

              colorojos text NOT NULL,

              idioma text NOT NULL,

              servicios text NOT NULL,

  nacionalidad text NOT NULL,
  fecha_actualizacion datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  user_id mediumint(9) NOT NULL,

  PRIMARY KEY  (id)

) $charset_collate;";



require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

dbDelta( $sql );

}

//Mostrar Cantidad de creditos por usuario

add_filter('manage_users_columns', 'my_custom_user_column');

function my_custom_user_column($columns) {

    $columns['credits'] = 'Credits';

    return $columns;

}



add_action('manage_users_custom_column', 'my_custom_user_column_content', 10, 3);

function my_custom_user_column_content($value, $column_name, $user_id) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'users';

    if ($column_name == 'credits') {

        $credits = $wpdb->get_var("SELECT credits FROM $table_name WHERE ID = $user_id");

        return $credits;

    }

    return $value;

}

add_action('user_edit_form_tag', 'my_custom_user_profile_fields');

function my_custom_user_profile_fields($user) {

if (!current_user_can('manage_options')) {

return;

}

    global $wpdb;

    $table_name = $wpdb->prefix . 'users';

    $credits = $wpdb->get_var("SELECT credits FROM $table_name WHERE ID = $user->ID");



    ?>



    <table class="form-table">

        <tr>

            <th><label for="credits">Creditos</label></th>

            <td>

                <input type="text" name="credits" id="credits" value="<?php echo $credits; ?>" class="regular-text" />

                <button type="button" id="update-credits" class="button-primary">Añadir Creditos</button>

                <span class="spinner" style="float: none;"></span>

                <p class="description">Ingrese la cantidad de creditos que quiera añadir.</p>

            </td>

        </tr>

    </table>

    <?php

}



add_action('admin_footer-user-edit.php', 'my_custom_user_profile_scripts');

function my_custom_user_profile_scripts() {

    ?>

    <script type="text/javascript">

    jQuery(document).ready(function($) {

        $('#update-credits').click(function() {

            var user_id = $(this).closest('form').find('input[name="user_id"]').val();

            var credits = $('#credits').val();

            $('.spinner').addClass('is-active');

            $.ajax({

                url: ajaxurl,

                type: 'POST',

                data: {

                    action: 'update_user_credits',

                    user_id: user_id,

                    credits: credits

                },

                success: function(response) {

                    $('.spinner').removeClass('is-active');

                    alert('Créditos agregados exitosamente.');

                }

                

            });

        });

    });

    </script>

    <?php

}



add_action('wp_ajax_update_user_credits', 'my_custom_update_user_credits');

function my_custom_update_user_credits() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'users';

    $user_id = intval($_POST['user_id']);

    $credits = intval($_POST['credits']);

    $current_credits = $wpdb->get_var("SELECT credits FROM $table_name WHERE ID = $user_id");

    $new_credits = $current_credits + $credits;

    $wpdb->update(

        $table_name,

        array(

            'credits' => $new_credits

        ),

        array(

            'ID' => $user_id

        ),

        array(

            '%d'

        ),

        array(

            '%d'

        )

    );

    wp_die();

}

function destacados_form_shortcode() {

    if ( ! is_user_logged_in() ) {

        return '<p>Debes iniciar sesión para acceder a este formulario.</p>';

    } $user_id = get_current_user_id();

    global $wpdb;

    $table_name = $wpdb->prefix . 'destacados';

    $result = $wpdb->get_row("SELECT * FROM $table_name WHERE user_id = $user_id");

    // Si el usuario ya ha llenado el formulario previamente, se muestran los datos en el formulario

    if ($result) {

        $imagenes = $result->imagenes;

        $nombre = $result->nombre;

        $frecuencia = $result->frecuencia;

        $numero_contacto = $result->numero_contacto;

        $whatsapp = $result->whatsapp;

        $edad = $result->edad;

        $ubicacion = $result->ubicacion;

        $nacionalidad = $result->nacionalidad;

        $peso = $result->peso;

        $altura = $result->altura;

        $descripcion = $result->descripcion;

         $disponibilidad_str = $result->disponibilidad;

        $disponibilidad = explode(',', $disponibilidad_str);

        $piel = $result->piel;

        $largocabello = $result->largocabello;

        $colorcabello = $result->colorcabello;

        $tamañopecho = $result->tamañopecho;

        $complexion = $result->complexion;

        $fumadora = $result->fumadora;

        $tarifas = $result->tarifas;

        $moneda = $result->moneda;

        $pechooperado = $result->pechooperado;

        $orientacion = $result->orientacion;

        $colorojos = $result->colorojos;

        $idioma = $result->idioma;

        $servicios_str = $result->servicios;

        $servicios = explode(',', $servicios_str);

        // Convierte la lista de imágenes en un array y muestra las imágenes existentes

        $imagenes_array = explode(',', $imagenes);

        $imagenes_html = '';

        $url = '<label>Link del anuncio</label><br><a href="'. $result->url .'">'. $result->url .'</a>';

        foreach ($imagenes_array as $imagen) {

            if ($imagen != '') {

                $imagenes_html .= '<div class="img-form"><img src="' . $imagen . '" width="100" height="100"></div>';

            }

        }

    } else {

     $imagenes = '';

     $id = '';

     $imagenes_actuales = '';

        $imagenes_html = '';

        $nombre = '';

        $url = '';

        $frecuencia = '';

        $numero_contacto = '';

        $whatsapp = '';

        $edad = '';

        $ubicacion = '';

        $peso = '';

        $altura = '';

        $descripcion = '';

        $disponibilidad = '';

        $nacionalidad = '';

        $piel = '';

        $largocabello = '';

        $colorcabello = '';

        $tamañopecho = '';

        $complexion = '';

        $fumadora = '';

        $tarifas = '';

        $moneda = '';

        $pechooperado = '';

        $orientacion = '';

        $colorojos = '';

        $idioma = '';

        $servicios = '';

    }



    // Aquí se muestra el formulario vacío

    ?>

    <form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" class="form-destacados" method="post" enctype="multipart/form-data">
      <p class="link"><?php echo $url; ?></p>
<div class="izq"> 
            <div class="imagenes-subidas"><?php echo $imagenes_html; ?></div>

        <label for="imagenes" class="imagenes">Imágenes: (SOLO 8 IMAGENES Y DE MAXIMO 2MB CADA UNA</label>

        <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*" value="">

        <div id="preview-images"></div>

        <label for="nombre">Nombre:</label><br>

        <input type="text" name="nombre" id="nombre" value="<?php echo $nombre; ?>" required>

        <br>

        <label for="numero_contacto">Numero:(Solo Numero 341245123)</label><br>

        <input type="number" name="numero_contacto" id="numero_contacto" value="<?php echo $numero_contacto; ?>" required>

        <br>

        <label for="whatsapp">Whatsapp: (Solo Numero 346245123)</label><br>

        <input type="number" name="whatsapp" id="whatsapp" value="<?php echo $whatsapp; ?>" required>

        <br>
        <script>
function limitDigits(input, maxLength) {
    const value = input.value;
    if (value.length > maxLength) {
        input.value = value.slice(0, maxLength);
    }
}
</script>
        <label for="peso">Peso (Kg)</label><br>
        <input type="number" name="peso" id="peso" value="<?php echo $peso; ?>" oninput="limitDigits(this, 3);">

        <br>

         <label for="altura">Altura (cm)</label><br>

        <input type="number" name="altura" id="altura" value="<?php echo $altura; ?>" maxlength="3" oninput="limitDigits(this, 3);">

        <br>

        <label for="descripcion">Descripcion:</label><br>
        <textarea name="descripcion" id="descripcion" rows="4" cols="50">
<?php echo $descripcion; ?>
</textarea>

        <br>
<label for="disponibilidad">Disponibilidad:</label>
<br>  
<div class="dispo"> 
      <div class="domicilio"> 
        <input type="checkbox" name="disponibilidad[]" value="domicilios" <?php if(is_array($disponibilidad) && in_array('domicilios', $disponibilidad)) { echo 'checked'; } ?>>
<label for="domicilios">Domicilios</label>
</div>
<div class="salidas"> 
<input type="checkbox" name="disponibilidad[]" value="salidas" <?php if(is_array($disponibilidad) && in_array('salidas', $disponibilidad)) { echo 'checked'; } ?>>
<label for="salidas">Salidas</label><br>
</div>
</div>
<label for="ubicacion">Ubicación:</label><br>

         <select name="ubicacion">

  <option value="Sevilla" <?php if ($ubicacion == "Sevilla") echo 'selected'; ?>>Sevilla</option>

  <option value="Alcala de Guadaira" <?php if ($ubicacion == "Alcala de Guadaira") echo 'selected'; ?>>Alcalá de Guadaira</option>

</select>

        <br>

        <label for="edad">Edad:</label><br>

      <select name="edad">

  <option value="18" <?php if ($edad == 18) echo 'selected'; ?>>18 años</option>

  <option value="19" <?php if ($edad == 19) echo 'selected'; ?>>19 años</option>

  <option value="20" <?php if ($edad == 20) echo 'selected'; ?>>20 años</option>

  <option value="21" <?php if ($edad == 21) echo 'selected'; ?>>21 años</option>

  <option value="22" <?php if ($edad == 22) echo 'selected'; ?>>22 años</option>

  <option value="23" <?php if ($edad == 23) echo 'selected'; ?>>23 años</option>

  <option value="24" <?php if ($edad == 24) echo 'selected'; ?>>24 años</option>

  <option value="25" <?php if ($edad == 25) echo 'selected'; ?>>25 años</option>

  <option value="26" <?php if ($edad == 26) echo 'selected'; ?>>26 años</option>

  <option value="27" <?php if ($edad == 27) echo 'selected'; ?>>27 años</option>

  <option value="28" <?php if ($edad == 28) echo 'selected'; ?>>28 años</option>

  <option value="29" <?php if ($edad == 29) echo 'selected'; ?>>29 años</option>

  <option value="30" <?php if ($edad == 30) echo 'selected'; ?>>30 años</option>

  <option value="31" <?php if ($edad == 31) echo 'selected'; ?>>31 años</option>

  <option value="32" <?php if ($edad == 32) echo 'selected'; ?>>32 años</option>

  <option value="33" <?php if ($edad == 33) echo 'selected'; ?>>33 años</option>

  <option value="34" <?php if ($edad == 34) echo 'selected'; ?>>34 años</option>

  <option value="35" <?php if ($edad == 35) echo 'selected'; ?>>35 años</option>

  <option value="36" <?php if ($edad == 36) echo 'selected'; ?>>36 años</option>

  <option value="37" <?php if ($edad == 37) echo 'selected'; ?>>37 años</option>

  <option value="38" <?php if ($edad == 38) echo 'selected'; ?>>38 años</option>

  <option value="39" <?php if ($edad == 39) echo 'selected'; ?>>39 años</option>

  <option value="40" <?php if ($edad == 40) echo 'selected'; ?>>40 años</option>

  <option value="41" <?php if ($edad == 41) echo 'selected'; ?>>41 años</option>

  <option value="42" <?php if ($edad == 42) echo 'selected'; ?>>42 años</option>

  <option value="43" <?php if ($edad == 43) echo 'selected'; ?>>43 años</option>

  <option value="44" <?php if ($edad == 44) echo 'selected'; ?>>44 años</option>

  <option value="45" <?php if ($edad == 45) echo 'selected'; ?>>45 años</option>

  <option value="46" <?php if ($edad == 46) echo 'selected'; ?>>46 años</option>

  <option value="47" <?php if ($edad == 47) echo 'selected'; ?>>47 años</option>

  <option value="48" <?php if ($edad == 48) echo 'selected'; ?>>48 años</option>

  <option value="49" <?php if ($edad == 49) echo 'selected'; ?>>49 años</option>

  <option value="50" <?php if ($edad == 50) echo 'selected'; ?>>50 años</option>

  <!-- Otras opciones -->

</select><br>

<label for="nacionalidad">Nacionalidad:</label><br>

        <select name="nacionalidad" id="nacionalidad">

                            <option value="0" <?php if ($nacionalidad == 0) echo 'selected'; ?> >Selecciona</option>

                            <option value="Latina" <?php if ($nacionalidad == 'Latina') echo 'selected'; ?>>Colombiana</option>

                            <option value="Caucasica" <?php if ($nacionalidad == 'Caucasica') echo 'selected'; ?>>Mexicana</option>

                            <option value="Negro" <?php if ($nacionalidad == 'Negro') echo 'selected'; ?>>Venezolana</option>

                            <option value="Blanco" <?php if ($nacionalidad == 'Blanco') echo 'selected'; ?>>Brasileña</option>

                            <option value="Oriente Medio" <?php if ($nacionalidad == 'Oriente Medio') echo 'selected'; ?>>Oriente Medio</option>

                            <option value="Asiatica" <?php if ($nacionalidad == 'Asiatica') echo 'selected'; ?>>Rumana</option>

                            <option value="India" <?php if ($nacionalidad == 'India') echo 'selected'; ?>> Bulgara</option>

                            <option value="China" <?php if ($nacionalidad == 'China') echo 'selected'; ?>>China</option>

                            <option value="Nativa Americana" <?php if ($nacionalidad == 'Nativa Americana') echo 'selected'; ?>>Española</option>

                            <option value="Otro" <?php if ($nacionalidad == 'Otro') echo 'selected'; ?>>Otro</option>

                    </select>

<br>    

                    <label for="piel">Piel:</label><br>

        <select name="piel" id="piel">

            <option value="0" <?php if ($piel == 0) echo 'selected'; ?>>Selecciona</option>

                            <option value="Latina" <?php if ($piel == 'Latina') echo 'selected'; ?>>Latina</option>

                            <option value="Caucasica" <?php if ($piel == 'Caucasica') echo 'selected'; ?>>Caucásica</option>

                            <option value="Negro" <?php if ($piel == 'Negro') echo 'selected'; ?>>Negra</option>

                            <option value="Blanco" <?php if ($piel == 'Blanco') echo 'selected'; ?>>Blanca</option>

                            <option value="Oriente Medio" <?php if ($piel == 'Oriente Medio') echo 'selected'; ?>>Oriente Medio</option>

                            <option value="Asiatica" <?php if ($piel == 'Asiatica') echo 'selected'; ?>>Asiática</option>j

                            <option value="India" <?php if ($piel == 'India') echo 'selected'; ?>>India</option>

                            <option value="China" <?php if ($piel == 'China') echo 'selected'; ?>>China</option>

                            <option value="Nativa Americana" <?php if ($piel == 'Nativa Americana') echo 'selected'; ?>>Nativa Americana</option>

                            <option value="Otro" <?php if ($piel == 'Otro') echo 'selected'; ?>>Otro</option>

                    </select> <br>  
</div>
<div class="der"> 
                    <label for="largocabello">Largo del Cabello:</label><br>

                    <select name="largocabello" id="largocabello" class="largocabello">

            <option value="0" <?php if ($largocabello == 0) echo 'selected'; ?>>Selecciona</option>

                            <option value="Muy Corto" <?php if ($largocabello == 'Muy Corto') echo 'Muy Corto'; ?>>Muy Corto</option>

                            <option value="Corto" <?php if ($largocabello == 'Corto') echo 'selected'; ?>>Corto</option>

                            <option value="Por los hombros" <?php if ($largocabello == 'Por los hombros') echo 'selected'; ?>>Por los hombros</option>

                            <option value="Largo" <?php if ($largocabello == 'Largo') echo 'selected'; ?>>Largo</option>

                            <option value="Muy largo" <?php if ($largocabello == 'Muy largo') echo 'selected'; ?>>Muy largo</option>

                    </select>

                    <br>    

                    <label for="piel">Color del Cabello:</label><br>

                    <select name="colorcabello" id="colorcabello" class="colorcabello">

            <option value="0" <?php if ($colorcabello == 0) echo 'selected'; ?>>Selecciona</option>

                            <option value="Negro" <?php if ($colorcabello == 'Negro') echo 'selected'; ?>>Negro</option>

                            <option value="Rubio" <?php if ($colorcabello == 'Rubio') echo 'selected'; ?>>Rubio</option>

                            <option value="Marron" <?php if ($colorcabello == 'Marron') echo 'selected'; ?>>Marron</option>

                            <option value="Castaño" <?php if ($colorcabello == 'Castaño') echo 'selected'; ?>>Castaño</option>

                            <option value="Rubio oscuro" <?php if ($colorcabello == 'Rubio oscuro') echo 'selected'; ?>>Rubio oscuro</option>

                            <option value="Dorado" <?php if ($colorcabello == 'Dorado') echo 'selected'; ?>>Dorado</option>

                            <option value="Rojo" <?php if ($colorcabello == 'Rojo') echo 'selected'; ?>>Rojo</option>

                            <option value="Gris" <?php if ($colorcabello == 'Gris') echo 'selected'; ?>>Gris</option>

                            <option value="Plata" <?php if ($colorcabello == 'Plata') echo 'selected'; ?>>Plata</option>

                            <option value="Blanco" <?php if ($colorcabello == 'Blanco') echo 'selected'; ?>>Blanco</option>

                            <option value="Otro" <?php if ($colorcabello == 'Otro') echo 'selected'; ?>>Otro</option>

                    </select>

                    <br>    

                     <label for="tamañopecho">Tamaño del pecho:</label><br>           

                    <select name="tamañopecho" id="tamañopecho" class="tamañopecho">

            <option value="0" <?php if ($tamañopecho == 0) echo 'selected'; ?>>Selecciona</option>

                            <option value="Muy pequeño" <?php if ($tamañopecho == 'Muy pequeño') echo 'selected'; ?>>Muy pequeño</option>

                            <option value="Pequeño (A)" <?php if ($tamañopecho == 'Pequeño (A)') echo 'selected'; ?>>Pequeño (A)</option>

                            <option value="Mediano (B)" <?php if ($tamañopecho == 'Mediano (B)') echo 'selected'; ?>>Mediano (B)</option>

                            <option value="Grande (G)" <?php if ($tamañopecho == 'Grande (G)') echo 'selected'; ?>>Grande (G)</option>

                            <option value="Muy Grandes (D)" <?php if ($tamañopecho == 'Muy Grandes (D)') echo 'selected'; ?>>Muy Grandes (D)</option>

                            <option value="Enormes (E+)" <?php if ($tamañopecho == 'Enormes (E+)') echo 'selected'; ?>>Enormes (E+)</option>

                    </select>

                    <br>    

                    <label for="complexion">Complexión:</label><br>           

                   <select name="complexion" id="complexion" class="complexion">

            <option value="0" <?php if ($complexion == 0) echo 'selected'; ?>>Selecciona</option>

                            <option value="Muy delgada" <?php if ($complexion == 'Muy delgada') echo 'selected'; ?>>Muy delgada</option>

                            <option value="Delgada" <?php if ($complexion == 'Delgada') echo 'selected'; ?>>Delgada</option>

                            <option value="Normal" <?php if ($complexion == 'Normal') echo 'selected'; ?>>Normal</option>

                            <option value="Curvy" <?php if ($complexion == 'Curvy') echo 'selected'; ?>>Curvy</option>

                            <option value="Gorda" <?php if ($complexion == 'Gorda') echo 'selected'; ?>>Gorda</option>

                    </select><br>   

                    <label for="fumadora">Fumadora:</label><br> 

       <select name="fumadora" id="fumadora" class="fumadora">

                            <option value="Si" <?php if ($fumadora == 'Si') echo 'selected'; ?>>Si</option>

                            <option value="No" <?php if ($fumadora == 'No') echo 'selected'; ?>>No</option>

                    </select><br>   

                       <label for="moneda">Tarifas por Hora:</label><br> 

                    <select name="moneda" id="moneda">

                <option value="MXN">MXN - Mexican peso</option>

<option value="USD" <?php if ($moneda == 'USD') echo 'selected'; ?>>USD - Dolar Estadounidense</option>

<option value="EUR" <?php if ($moneda == 'EUR') echo 'selected'; ?>>EUR - Euro</option>

<option value="BGN" <?php if ($moneda == 'BGN') echo 'selected'; ?>>BGN - Lev Búlgaro</option>

<option value="CAD" <?php if ($moneda == 'CAD') echo 'selected'; ?>>CAD - Dólar Canadiense</option>

<option value="CHF" <?php if ($moneda == 'CHF') echo 'selected'; ?>>CHF - Franco Suizo</option>

<option value="CZK" <?php if ($moneda == 'CZK') echo 'selected'; ?>>CZK - Corona Checa</option>

<option value="DKK" <?php if ($moneda == 'DKK') echo 'selected'; ?>>DKK - Corona Danesa</option>

<option value="GBP" <?php if ($moneda == 'GBP') echo 'selected'; ?>>GBP - Libra Esterlina</option>

<option value="HKD" <?php if ($moneda == 'HKD') echo 'selected'; ?>>HKD - Dólar de Hong Kong</option>

<option value="HUF" <?php if ($moneda == 'HUF') echo 'selected'; ?>>HUF - Florin Hungaro</option>

<option value="MKD" <?php if ($moneda == 'MKD') echo 'selected'; ?>>MKD - Denar macedonio</option>

<option value="MYR" <?php if ($moneda == 'MYR') echo 'selected'; ?>>MYR - Ringgit malayo</option>

<option value="NOK" <?php if ($moneda == 'NOK') echo 'selected'; ?>>NOK - Corona Noruega</option>

<option value="NZD" <?php if ($moneda == 'NZD') echo 'selected'; ?>>NZD - Dólar de  Nueva Zelanda</option>

<option value="PLN" <?php if ($moneda == 'PLN') echo 'selected'; ?>>PLN - Esloti Polaco</option>

<option value="RON" <?php if ($moneda == 'RON') echo 'selected'; ?>>RON - Nuevo leu rumano</option>

<option value="SEK" <?php if ($moneda == 'SEK') echo 'selected'; ?>>SEK - Corona Sueca</option>

            </select>

        <input type="number" name="tarifas" id="tarifas" value="<?php echo $tarifas; ?>" maxlength="3" oninput="limitDigits(this, 3);"><br>    

        <label for="pechooperado">Pecho Operado:</label>

        <br>

        <select name="pechooperado" id="pechooperado">

        <option value="Si" <?php if ($pechooperado == 'Si') echo 'selected'; ?>>Si</option>

        <option value="No" <?php if ($pechooperado == 'No') echo 'selected'; ?>>No</option>

        </select><br>   

        <label for="orientacion">Orientacion Sexual:</label><br>

        <input type="text" name="orientacion" id="orientacion" value="<?php echo $orientacion; ?>">

        <br>    

        <label for="colorojos">Color De Ojos:</label><br>

        <input type="text" name="colorojos" id="colorojos" value="<?php echo $colorojos; ?>"><br>   

        <label for="idioma">Idioma:</label><br>

        <input type="text" name="idioma" id="idioma" value="<?php echo $idioma; ?>"><br>    

        <label for="servicios">Servicios:</label><br>

          <div class="form-input">
               <?php
          $servicios_disponibles = array(
             'Oral Natural', 'Sexo Oral', 'Eyacular en boca', 'Eyacular en la cara', 'Eyacular en cuerpo', 'Tragado',
              'Besos en boca', 'Sexo Anal', 'Lesbico', 'BDSM', 'Striptease/Lapdance', 'Masaje Erótico', 'Lluvias', 'Parejas',
             'GFE - Trato de Novios', 'Tríos', 'Fetichismo', 'Juguetes Eróticos', 'Varias Relaciones', 'Dominación', 'Toda la Noche'
              );
         foreach ($servicios_disponibles as $servicio) {
             $checked = in_array($servicio, $servicios) ? 'checked' : '';
                  ?>
              <div class="one-servicio">
                 <label for="servicios_<?php echo $servicio; ?>">
                      <input type="checkbox" name="servicios[]" value="<?php echo $servicio; ?>" id="servicios_<?php echo $servicio; ?>" <?php echo $checked; ?>>
                     <?php echo $servicio; ?>
                 </label>
              </div>
             <?php
                   }
         ?>
            </div> <!-- form-input --><br>  

        <p> <?php echo do_shortcode('[show_user_credits]'); ?> </p>

        <label for="frecuencia">Frecuencia de Actualizacion:</label>

        <br>

        <select name="frecuencia" id="frecuencia">

        <option value="0" <?php if ($frecuencia == 0) echo 'selected'; ?>>Anuncio Pausado</option>

        <option value="3" <?php if ($frecuencia == 3) echo 'selected'; ?>> 3 minutos</option>

        <option value="5" <?php if ($frecuencia == 5) echo 'selected'; ?>> 5 Minutos</option>

        <option value="10" <?php if ($frecuencia == 10) echo 'selected'; ?>> 10 Minutos</option>

        <option value="15" <?php if ($frecuencia == 15) echo 'selected'; ?>> 15 Minutos</option>

        </select>

        
</div>
<br>  <br>
        <input class="btn-send" type="submit" name="destacados_form_submit" value="Subir imágenes">

    </form>

    <script>

        document.getElementById('imagenes').addEventListener('change', function() {

          const previewImages = document.getElementById('preview-images');

          previewImages.innerHTML = '';

          const files = this.files;

          for (let i = 0; i < files.length; i++) {

            const file = files[i];

            const image = document.createElement('img');

            image.src = URL.createObjectURL(file);

            image.style.height = '100px';

            image.style.marginRight = '10px';

            previewImages.appendChild(image);

          }

        });

    </script>

    <?php

    return ob_get_clean();

}

add_shortcode( 'destacados_form', 'destacados_form_shortcode' );

function copiar_anuncios_a_tablas_especificas() {
    global $wpdb;

    // Seleccionar los valores de ubicación únicos en la tabla 'destacados'
    $ubicaciones = $wpdb->get_col("SELECT DISTINCT ubicacion FROM $wpdb->prefix" . "destacados");

    // Crear una lista de tablas específicas para cada ubicación y copiar los anuncios a las tablas específicas correspondientes
    foreach ($ubicaciones as $ubicacion) {
        $tabla_especifica = $wpdb->prefix . 'destacados_' . str_replace(' ', '_', strtolower($ubicacion));

        // Verificar si la tabla específica ya existe, y crearla si es necesario
        if ($wpdb->get_var("SHOW TABLES LIKE '$tabla_especifica'") !== $tabla_especifica) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $tabla_especifica (
              id mediumint(9) NOT NULL AUTO_INCREMENT,
              nombre text NOT NULL,
              whatsapp text NOT NULL,
              numero_contacto text NOT NULL,
              edad text NOT NULL,
              descripcion text NOT NULL,
              ubicacion text NOT NULL,
              imagenes text NOT NULL,
              frecuencia text NOT NULL,
              posicion text NOT NULL,
              url text NOT NULL,
              peso text NOT NULL,
              altura text NOT NULL,
              disponibilidad text NOT NULL,
              piel text NOT NULL,
              largocabello text NOT NULL,
              colorcabello text NOT NULL,
              tamañopecho text NOT NULL,
              complexion text NOT NULL,
              fumadora text NOT NULL,
              tarifas text NOT NULL,
              moneda text NOT NULL,
              pechooperado text NOT NULL,
              orientacion text NOT NULL,
              colorojos text NOT NULL,
              idioma text NOT NULL,
              servicios text NOT NULL,
              nacionalidad text NOT NULL,
              fecha_actualizacion datetime NOT NULL,
              user_id mediumint(9) NOT NULL,
              PRIMARY KEY  (id),
              UNIQUE KEY user_id_unique (user_id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        // Escapar la variable de ubicación
        $ubicacion_escaped = esc_sql($ubicacion);

        // Obtener los anuncios de la ubicación actual
        $anuncios = $wpdb->get_results("SELECT * FROM $wpdb->prefix" . "destacados WHERE ubicacion = '$ubicacion_escaped'");

        // Iterar sobre los anuncios y copiar los datos a la tabla específica correspondiente
        foreach ($anuncios as $anuncio) {
            // Verificar si el registro ya existe en la tabla específica
            $registro_existente = $wpdb->get_row("SELECT * FROM $tabla_especifica WHERE user_id = {$anuncio->user_id}");

            // Si el registro no existe, copiar todos los datos, incluida la fecha de actualización
            if (!$registro_existente) {
                $wpdb->insert(
                    $tabla_especifica,
                    array(
                        // (Aquí debes agregar todos los campos y sus valores correspondientes al array, tal como lo hiciste en la consulta SQL original)
                        'nombre' => $anuncio->nombre,
                        'whatsapp' => $anuncio->whatsapp,
                        'numero_contacto' => $anuncio->numero_contacto,
                        'edad' => $anuncio->edad,
                        'descripcion' => $anuncio->descripcion,
                        'ubicacion' => $anuncio->ubicacion,
                        'imagenes' => $anuncio->imagenes,
                        'frecuencia' => $anuncio->frecuencia,
                        'posicion' => $anuncio->posicion,
                        'url' => $anuncio->url,
                        'moneda' => $anuncio->moneda,
                        'fecha_actualizacion' => $anuncio->fecha_actualizacion,
                        'user_id' => $anuncio->user_id,
                        'tarifas' => $anuncio->tarifas,
                    )
                );
            } else {
                $wpdb->update(
      $tabla_especifica,
      array(
          // (Aquí debes agregar todos los campos y sus valores correspondientes al array, excepto la fecha de actualización)
          'nombre' => $anuncio->nombre,
          'whatsapp' => $anuncio->whatsapp,
          'numero_contacto' => $anuncio->numero_contacto,
          'edad' => $anuncio->edad,
          'moneda' => $anuncio->moneda,
          'frecuencia' => $anuncio->frecuencia,
          'descripcion' => $anuncio->descripcion,
          'ubicacion' => $anuncio->ubicacion,
          'imagenes' => $anuncio->imagenes,
           'tarifas' => $anuncio->tarifas,
          'url' => $anuncio->url
      ),
      array('user_id' => $anuncio->user_id)
  );
            }
        }
     }
}

add_action('cron_ejecutar_copia_anuncios_especificas', 'copiar_anuncios_a_tablas_especificas');

// Agregar tarea al cron

function agregar_tarea_cron_copia_anuncios_especificas() {

    if (!wp_next_scheduled('cron_ejecutar_copia_anuncios_especificas')) {

        wp_schedule_event(time(), 'minutely', 'cron_ejecutar_copia_anuncios_especificas');

    }

}

add_action('wp', 'agregar_tarea_cron_copia_anuncios_especificas');



function get_user_credits($user_id) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'users';

    $credits = $wpdb->get_var("SELECT credits FROM $table_name WHERE ID = $user_id");

    return $credits;

}

function show_user_credits_shortcode($atts) {

    $user_id = get_current_user_id();

    $credits = get_user_credits($user_id);

    return 'Tienes ' . $credits . ' créditos.';

}

add_shortcode('show_user_credits', 'show_user_credits_shortcode');

function destacados_form_handler() {
    if (isset($_POST['destacados_form_submit'])) {
        $user_id = get_current_user_id();

        global $wpdb;

        $table_name = $wpdb->prefix . 'destacados';

        $result = $wpdb->get_row("SELECT * FROM $table_name WHERE user_id = $user_id");

        $nombre = sanitize_text_field($_POST['nombre']);
        $numero_contacto = sanitize_text_field($_POST['numero_contacto']);
        $whatsapp = sanitize_text_field($_POST['whatsapp']);
        $edad = sanitize_text_field($_POST['edad']);
        $frecuencia = sanitize_text_field($_POST['frecuencia']);
        $ubicacion = sanitize_text_field($_POST['ubicacion']);
        $descripcion = sanitize_text_field($_POST['descripcion']);
        $peso = sanitize_text_field($_POST['peso']);
        $altura = sanitize_text_field($_POST['altura']);
        $nacionalidad = sanitize_text_field($_POST['nacionalidad']);
        $existing_page = get_page_by_title( $nombre, OBJECT, 'post' );
        $piel = sanitize_text_field($_POST['piel']);
        $largocabello = sanitize_text_field($_POST['largocabello']);
        $colorcabello = sanitize_text_field($_POST['colorcabello']);
        $tamañopecho = sanitize_text_field($_POST['tamañopecho']);
        $complexion = sanitize_text_field($_POST['complexion']);
        $fumadora = sanitize_text_field($_POST['fumadora']);
        $tarifas = sanitize_text_field($_POST['tarifas']);
        $moneda = sanitize_text_field($_POST['moneda']);
        $pechooperado = sanitize_text_field($_POST['pechooperado']);
        $orientacion = sanitize_text_field($_POST['orientacion']);
        $colorojos = sanitize_text_field($_POST['colorojos']);
        $idioma = sanitize_text_field($_POST['idioma']);
        $servicios = $result->servicios;
         // Obtener la disponibilidad

        if (isset($_POST['disponibilidad']) && is_array($_POST['disponibilidad'])) {
            $disponibilidad = $_POST['disponibilidad'];
            $disponibilidad_str = implode(',', $disponibilidad);
        } else {
            $disponibilidad_str = '';
        }
        if (isset($_POST['servicios']) && is_array($_POST['servicios'])) {
            $servicios = $_POST['servicios'];
            $servicios_str = implode(',', $servicios);
        } else {
            $servicios_str = '';
        }
        $imagenes_actuales = isset($result->imagenes) ? $result->imagenes : '';
            $ubicaciones = $wpdb->get_col("SELECT DISTINCT ubicacion FROM $wpdb->prefix" . "destacados");
    foreach ($ubicaciones as $ubicacion_verificar) {
        if ($ubicacion_verificar !== $ubicacion) {
            $tabla_verificar = $wpdb->prefix . 'destacados_' . str_replace(' ', '_', strtolower($ubicacion_verificar));
            $existing_destacado = $wpdb->get_row("SELECT * FROM $tabla_verificar WHERE user_id = $user_id");
            if ($existing_destacado) {
                $wpdb->delete(
                    $tabla_verificar,
                    array('user_id' => $user_id)
                );
                break; // Salir del bucle ya que se encontró la tabla donde estaba el anuncio
            }
        }
    }
        // Obtiene las nuevas imágenes del formulario
        $imagenes_nuevas = $_FILES['imagenes'];
        // Si se han seleccionado nuevas imágenes, se reemplazan las imágenes actuales
        if (!empty($imagenes_nuevas['name'][0])) {
            // Limita la cantidad máxima de imágenes a 8
            if (count($imagenes_nuevas['name']) > 8) {
                echo '<div class="error">Solo puedes subir hasta 8 imágenes.</div>';
                return;
            }
            $imagen_urls = array();
            for ($i = 0; $i < count($imagenes_nuevas['name']); $i++) {
                // Limita el tamaño máximo de cada imagen a 2MB
                if ($imagenes_nuevas['size'][$i] > 2 * 1024 * 1024) {
                    echo '<div class="error">Una o más imágenes superan el tamaño máximo permitido de 2MB.</div>';
                    return;
                }
                $imagen = wp_upload_bits($imagenes_nuevas['name'][$i], null, file_get_contents($imagenes_nuevas['tmp_name'][$i]));
                $imagen_urls[] = $imagen['url'];
            }
            $imagenes = implode(',', $imagen_urls);
        } else {
            $imagenes = $imagenes_actuales;
        }
        // Si el usuario ya ha llenado el formulario previamente, se actualizan los datos en la base de datos
        if ($result) {
            $id = $result->id;
             $old_nombre = $result->nombre;
            // Verificar si el nombre ha cambiado
            if ($nombre !== $old_nombre) {
              
                // Actualizar el título y el slug de la página correspondiente
                $post = get_page_by_title( $old_nombre, OBJECT, 'post' );
                if ($post) {
                $post_content = '';

  $post_content = '<div class="superior"><div class="superior-right"><div class="datos"><h1>' . $nombre . '</h1>';
                    if ($ubicacion) {
                        $post_content .= '<p><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" version="1.1" id="Capa_1" width="800px" height="800px" viewBox="0 0 395.71 395.71" xml:space="preserve">
<g>
  <path d="M197.849,0C122.131,0,60.531,61.609,60.531,137.329c0,72.887,124.591,243.177,129.896,250.388l4.951,6.738   c0.579,0.792,1.501,1.255,2.471,1.255c0.985,0,1.901-0.463,2.486-1.255l4.948-6.738c5.308-7.211,129.896-177.501,129.896-250.388   C335.179,61.609,273.569,0,197.849,0z M197.849,88.138c27.13,0,49.191,22.062,49.191,49.191c0,27.115-22.062,49.191-49.191,49.191   c-27.114,0-49.191-22.076-49.191-49.191C148.658,110.2,170.734,88.138,197.849,88.138z"/>
</g>
</svg> ' . $ubicacion . '</p>';
                    }
                    if ($nacionalidad) {
                        $post_content .= '<p>Nacionalidad: ' . $nacionalidad . '</p>';
                    }
                    // Asumiendo que las variables $tarifas y $moneda ya tienen valores asignados
if ($tarifas && $moneda) {
    $post_content .= '<p><span class="precio">' . $tarifas . ' ' . strtoupper($moneda) . '/h</span></p></div>';
}
                    if ($edad) {
                        $post_content .= '<div class="datos-aba"><p><span>Edad:</span> ' . $edad . '</p>';
                    }
                    if ($altura) {
                        $post_content .= '<p><span>Altura:</span> ' . $altura . '</p>';
                    }
                    if ($peso) {
                        $post_content .= '<p><span>Peso:</span> ' . $peso . '</p>';
                    }
                    if ($complexion) {
                        $post_content .= '<p><span>Complexion:</span> ' . $complexion . '</p>';
                    }
                    if ($piel) {
                        $post_content .= '<p><span>Piel:</span> ' . $piel . '</p>';
                    }
                    if ($colorojos) {
                        $post_content .= '<p><span>Color de ojos:</span> ' . $colorojos . '</p>';
                    }
                    if ($largocabello) {
                        $post_content .= '<p><span>Largo del Cabello:</span> ' . $largocabello . '</p>';
                    }
                    if ($colorcabello) {
                        $post_content .= '<p><span>Color del cabello:</span> ' . $colorcabello . '</p>';
                    }
                    if ($tamañopecho) {
                        $post_content .= '<p><span>Tamaño de Pechos:</span> ' . $tamañopecho . '</p>';
                    }      
                    if ($pechooperado) {
                        $post_content .= '<p><span>Pecho Operado:</span> ' . $pechooperado . '</p>';
                    }
                    if ($orientacion) {
                        $post_content .= '<p><span>Orientación Sexual:</span> ' . $orientacion . '</p>';
                    }
                    if ($fumadora) {
                        $post_content .= '<p><span>Fumadora:</span> ' . $fumadora . '</p>';
                    }
                    if ($idioma) {
                        $post_content .= '<p><span>Idioma:</span> ' . $idioma . '</p>';
                    }
                    $post_content .= '</div></div></div> ';
                     if (isset($imagenes) && !empty($imagenes)) {
    $imagenes_urls = explode(',', $imagenes);

    // Obtener la primera imagen
    $primera_imagen = trim($imagenes_urls[0]);

    // Asegúrate de que la URL de la imagen es válida antes de agregarla al contenido del post
    if (!empty($primera_imagen)) {
        $post_content .= '<div class="img"><img width="30%" src="' . esc_url($primera_imagen) . '"></div></div>';
        // Agrega el resto de las imágenes al contenido
    } else {
        $post_content .= '<div class="img"><p>No hay imagen disponible</p></div></div>';
    }
}
$post_content .= ' </div><div class="contact">';
                    if ($numero_contacto) {
$telefono_svg_url = plugins_url('images/telefono.svg', __FILE__);
    $post_content .= '<a href="tel:' . $numero_contacto . '" class="telefono"><img src="' . $telefono_svg_url . '" alt="Teléfono">' . $numero_contacto . '</a>';
}
if ($whatsapp) {
  $blogName = get_bloginfo('name');
    $encodedBlogName = urlencode($blogName);
$whatsapp_svg_url = plugins_url('images/whatsapp.svg', __FILE__);
    $post_content .= '<a href="https://wa.me/' . $whatsapp . '?text=Hola%2C%20acabo%20de%20ver%20tu%20anuncio%20en%20' . $encodedBlogName . '%20me%20gustar%C3%ADa%20quedar%20contigo." class="whatsapp"><img src="' . $whatsapp_svg_url . '" alt="WhatsApp">' . $whatsapp . '</a>';
}
                    $post_content .= ' </div><div class="img-gallery">';
                    // Agregar las imágenes del anuncio destacado a la página
                    if ($imagenes) {
                        $imagenes_urls = explode(',', $imagenes);
                        foreach ($imagenes_urls as $imagen_url) {
                             $post_content .= '<div class="img-gallery-single"><img width="30%" src="' . $imagen_url . '"></div>';
                        }
                    }

                      if ($descripcion) {
                        $post_content .= '</div><div class="descripcion"><h3 class="h3">Sobre Mi:</h3><p>' . $descripcion . '</p></div>';
                    }
                    if (is_array($disponibilidad) && !empty($disponibilidad)) {
    $post_content .= '<div class="diservi"><div class="disp-lista"><p>Disponibilidad:</p>';
    $post_content .= '<ul>';
    foreach ($disponibilidad as $disponible) {
        $post_content .= '<li>' . $disponible . '</li>';
    }
    $post_content .= '</ul></div>';
}
if (is_array($servicios) && !empty($servicios)) {
    $post_content .= '<div class="servicios-lista"><p>Servicios:</p>';
    $post_content .= '<ul>';
    foreach ($servicios as $servicio) {
        $post_content .= '<li>' . $servicio . '</li>';
    }
    $post_content .= '</ul></div></div>';
}

  $post_content .= '</div><div class="contact">';
if ($numero_contacto) {
$telefono_svg_url = plugins_url('images/telefono.svg', __FILE__);
    $post_content .= '<a href="tel:' . $numero_contacto . '" class="telefono"><img src="' . $telefono_svg_url . '" alt="Teléfono">' . $numero_contacto . '</a>';
}
if ($whatsapp) {
  $blogName = get_bloginfo('name');
    $encodedBlogName = urlencode($blogName);
$whatsapp_svg_url = plugins_url('images/whatsapp.svg', __FILE__);
    $post_content .= '<a href="https://wa.me/' . $whatsapp . '?text=Hola%2C%20acabo%20de%20ver%20tu%20anuncio%20en%20' . $encodedBlogName . '%20me%20gustar%C3%ADa%20quedar%20contigo." class="whatsapp"><img src="' . $whatsapp_svg_url . '" alt="WhatsApp">' . $whatsapp . '</a>';
}
$post_content .= ' </div><div class="img-gallery">';
                    // Agregar las imágenes del anuncio destacado a la página
                    if ($imagenes) {
                        $imagenes_urls = explode(',', $imagenes);
                        foreach ($imagenes_urls as $imagen_url) {
                             $post_content .= '<div class="img-gallery-single"><img width="30%" src="' . $imagen_url . '"></div>';
                        }
                    }
                    $post_content .= '</div><div class="descripcion">';
                      if ($descripcion){
                        $post_content .= '<h3 class="h3">Sobre Mi:</h3><p>' . $descripcion . '</p>';
                    }
                    $post_content .= '</div><div class="diservi"><div class="disp-lista">';
                    if (is_array($disponibilidad) && !empty($disponibilidad)) {
    $post_content .= '<p>Disponibilidad:</p>';
    $post_content .= '<ul>';
    foreach ($disponibilidad as $disponible) {
        $post_content .= '<li>' . $disponible . '</li>';
    }
    $post_content .= '</ul>';
}
 $post_content .= '</div><div class="servicios-lista">';
if (is_array($servicios) && !empty($servicios)) {
    $post_content .= '<p>Servicios:</p>';
    $post_content .= '<ul>';
    foreach ($servicios as $servicio) {
        $post_content .= '<li>' . $servicio . '</li>';
    }
    $post_content .= '</ul>';
}
 $post_content .= '</div></div><div class="contact">';
if ($numero_contacto) {
$telefono_svg_url = plugins_url('images/telefono.svg', __FILE__);
    $post_content .= '<a href="tel:' . $numero_contacto . '" class="telefono"><img src="' . $telefono_svg_url . '" alt="Teléfono">' . $numero_contacto . '</a>';
}
if ($whatsapp) {
  $blogName = get_bloginfo('name');
    $encodedBlogName = urlencode($blogName);
$whatsapp_svg_url = plugins_url('images/whatsapp.svg', __FILE__);
    $post_content .= '<a href="https://wa.me/' . $whatsapp . '?text=Hola%2C%20acabo%20de%20ver%20tu%20anuncio%20en%20' . $encodedBlogName . '%20me%20gustar%C3%ADa%20quedar%20contigo." class="whatsapp"><img src="' . $whatsapp_svg_url . '" alt="WhatsApp">' . $whatsapp . '</a>';
}
$post_content .= '</div>';
                    $post_id = $post->ID;
                    wp_update_post(array(
                        'ID' => $post_id,
                        'post_title' => $nombre,
                        'post_name' => sanitize_title(str_replace(' ', '-', $nombre)) . '-' . $user_id,
                        'post_content' => $post_content,
                    ));
                    // Si ya existe una página con el mismo nombre, mostrar un mensaje de error
                }
                $wpdb->update(
                $table_name,
                array(
                    'imagenes' => $imagenes,
                    'nombre' => $nombre,
                    'url' => home_url('/destacados/' . sanitize_title(str_replace(' ', '-', $nombre)).'-'.$user_id),
                    'numero_contacto' => $numero_contacto,
                    'whatsapp' => $whatsapp,
                    'edad' => $edad,
                    'frecuencia' => $frecuencia,
                    'ubicacion' => $ubicacion,
                    'descripcion' => $descripcion,
                    'disponibilidad' => $disponibilidad_str,
                    'peso' => $peso,
                    'altura' => $altura,
                    'nacionalidad' => $nacionalidad,
                    'piel' => $piel,
                    'largocabello' => $largocabello,
                    'colorcabello' => $colorcabello,
                    'tamañopecho' => $tamañopecho,
                    'complexion' => $complexion,
                    'fumadora' => $fumadora,
                    'tarifas' => $tarifas,
                    'moneda' => $moneda,
                    'pechooperado' => $pechooperado,
                    'orientacion' => $orientacion,
                    'colorojos' => $colorojos,
                    'idioma' => $idioma,
                    'servicios' => $servicios_str,
                ),
                array(
                    'id' => $id
                )
            );
            }
            else { 
                   $post = get_page_by_title( $old_nombre, OBJECT, 'post' );
                if ($post) {
                   $post_content .= '';

   $post_content .= '<div class="superior"><div class="superior-right"><div class="datos">';
  $post_content .= '<h1>' . $nombre . '</h1>';
                    if ($ubicacion) {
                        $post_content .= '<p><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" version="1.1" id="Capa_1" width="800px" height="800px" viewBox="0 0 395.71 395.71" xml:space="preserve">
<g>
  <path d="M197.849,0C122.131,0,60.531,61.609,60.531,137.329c0,72.887,124.591,243.177,129.896,250.388l4.951,6.738   c0.579,0.792,1.501,1.255,2.471,1.255c0.985,0,1.901-0.463,2.486-1.255l4.948-6.738c5.308-7.211,129.896-177.501,129.896-250.388   C335.179,61.609,273.569,0,197.849,0z M197.849,88.138c27.13,0,49.191,22.062,49.191,49.191c0,27.115-22.062,49.191-49.191,49.191   c-27.114,0-49.191-22.076-49.191-49.191C148.658,110.2,170.734,88.138,197.849,88.138z"/>
</g>
</svg> ' . $ubicacion . '</p>';
                    }
                    if ($nacionalidad) {
                        $post_content .= '<p>Nacionalidad: ' . $nacionalidad . '</p>';
                    }
                    // Asumiendo que las variables $tarifas y $moneda ya tienen valores asignados
if ($tarifas && $moneda) {
    $post_content .= '<p><span class="precio">' . $tarifas . ' ' . strtoupper($moneda) . '/h</span></p>';
}
 $post_content .= '</div><div class="datos-aba">';

                    if ($edad) {
                        $post_content .= '<p><span>Edad:</span> ' . $edad . '</p>';
                    }
                    if ($altura) {
                        $post_content .= '<p><span>Altura:</span> ' . $altura . '</p>';
                    }
                    if ($peso) {
                        $post_content .= '<p><span>Peso:</span> ' . $peso . '</p>';
                    }
                    if ($complexion) {
                        $post_content .= '<p><span>Complexion:</span> ' . $complexion . '</p>';
                    }
                    if ($piel) {
                        $post_content .= '<p><span>Piel:</span> ' . $piel . '</p>';
                    }
                    if ($colorojos) {
                        $post_content .= '<p><span>Color de ojos:</span> ' . $colorojos . '</p>';
                    }
                    if ($largocabello) {
                        $post_content .= '<p><span>Largo del Cabello:</span> ' . $largocabello . '</p>';
                    }
                    if ($colorcabello) {
                        $post_content .= '<p><span>Color del cabello:</span> ' . $colorcabello . '</p>';
                    }
                    if ($tamañopecho) {
                        $post_content .= '<p><span>Tamaño de Pechos:</span> ' . $tamañopecho . '</p>';
                    }      
                    if ($pechooperado) {
                        $post_content .= '<p><span>Pecho Operado:</span> ' . $pechooperado . '</p>';
                    }
                    if ($orientacion) {
                        $post_content .= '<p><span>Orientación Sexual:</span> ' . $orientacion . '</p>';
                    }
                    if ($fumadora) {
                        $post_content .= '<p><span>Fumadora:</span> ' . $fumadora . '</p>';
                    }
                    if ($idioma) {
                        $post_content .= '<p><span>Idioma:</span> ' . $idioma . '</p>';
                    }
                    $post_content .= '</div></div>';
                     if (isset($imagenes) && !empty($imagenes)) {
    $imagenes_urls = explode(',', $imagenes);

    // Obtener la primera imagen
    $primera_imagen = trim($imagenes_urls[0]);

    // Asegúrate de que la URL de la imagen es válida antes de agregarla al contenido del post
    if (!empty($primera_imagen)) {
        $post_content .= '<div class="img"><img width="30%" src="' . esc_url($primera_imagen) . '"></div>';
        // Agrega el resto de las imágenes al contenido
    } else {
        $post_content .= '<div class="img"><p>No hay imagen disponible</p></div>';
    }
}



$post_content .= '</div><div class="contact">';
if ($numero_contacto) {
$telefono_svg_url = plugins_url('images/telefono.svg', __FILE__);
    $post_content .= '<a href="tel:' . $numero_contacto . '" class="telefono"><img src="' . $telefono_svg_url . '" alt="Teléfono">' . $numero_contacto . '</a>';
}
if ($whatsapp) {
  $blogName = get_bloginfo('name');
    $encodedBlogName = urlencode($blogName);
$whatsapp_svg_url = plugins_url('images/whatsapp.svg', __FILE__);
    $post_content .= '<a href="https://wa.me/' . $whatsapp . '?text=Hola%2C%20acabo%20de%20ver%20tu%20anuncio%20en%20' . $encodedBlogName . '%20me%20gustar%C3%ADa%20quedar%20contigo." class="whatsapp"><img src="' . $whatsapp_svg_url . '" alt="WhatsApp">' . $whatsapp . '</a>';
}
$post_content .= ' </div><div class="img-gallery">';

                    // Agregar las imágenes del anuncio destacado a la página
                    if ($imagenes) {
                        $imagenes_urls = explode(',', $imagenes);
                        foreach ($imagenes_urls as $imagen_url) {
                             $post_content .= '<div class="img-gallery-single"><img width="30%" src="' . $imagen_url . '"></div>';
                        }
                    }
                    $post_content .= '</div><div class="descripcion">';
                      if ($descripcion) {
                        $post_content .= '<h3 class="h3">Sobre Mi:</h3><p>' . $descripcion . '</p>';
                    }
                    $post_content .= '</div><div class="diservi"><div class="disp-lista">';
                    if (is_array($disponibilidad) && !empty($disponibilidad)) {
    $post_content .= '<p>Disponibilidad:</p>';
    $post_content .= '<ul>';
    foreach ($disponibilidad as $disponible) {
        $post_content .= '<li>' . $disponible . '</li>';
    }
    $post_content .= '</ul>';
}
 $post_content .= '</div><div class="servicios-lista">';
if (is_array($servicios) && !empty($servicios)) {
    $post_content .= '<p>Servicios:</p>';
    $post_content .= '<ul>';
    foreach ($servicios as $servicio) {
        $post_content .= '<li>' . $servicio . '</li>';
    }
    $post_content .= '</ul>';
}
 $post_content .= '</div></div><div class="contact">';
if ($numero_contacto) {
$telefono_svg_url = plugins_url('images/telefono.svg', __FILE__);
    $post_content .= '<a href="tel:' . $numero_contacto . '" class="telefono"><img src="' . $telefono_svg_url . '" alt="Teléfono">' . $numero_contacto . '</a>';
}
if ($whatsapp) {
  $blogName = get_bloginfo('name');
    $encodedBlogName = urlencode($blogName);
$whatsapp_svg_url = plugins_url('images/whatsapp.svg', __FILE__);
    $post_content .= '<a href="https://wa.me/' . $whatsapp . '?text=Hola%2C%20acabo%20de%20ver%20tu%20anuncio%20en%20' . $encodedBlogName . '%20me%20gustar%C3%ADa%20quedar%20contigo." class="whatsapp"><img src="' . $whatsapp_svg_url . '" alt="WhatsApp">' . $whatsapp . '</a>';
}
$post_content .= '</div>';
                    $post_id = $post->ID;
                    wp_update_post(array(
                        'ID' => $post_id,
                        'post_content' => $post_content,
                    ));}
                $wpdb->update(
                $table_name,
                array(
                    'imagenes' => $imagenes,
                    'nombre' => $nombre,
                    'url' => home_url('/destacados/' . sanitize_title(str_replace(' ', '-', $nombre)).'-'.$user_id),
                    'numero_contacto' => $numero_contacto,
                    'whatsapp' => $whatsapp,
                    'frecuencia' => $frecuencia,
                    'edad' => $edad,
                    'ubicacion' => $ubicacion,
                    'descripcion' => $descripcion,
                    'disponibilidad' => $disponibilidad_str,
                    'peso' => $peso,
                    'altura' => $altura,
                    'nacionalidad' => $nacionalidad,
                    'piel' => $piel,
                    'largocabello' => $largocabello,
                    'colorcabello' => $colorcabello,
                    'tamañopecho' => $tamañopecho,
                    'complexion' => $complexion,
                    'fumadora' => $fumadora,
                    'tarifas' => $tarifas,
                    'moneda' => $moneda,
                    'pechooperado' => $pechooperado,
                    'orientacion' => $orientacion,
                    'colorojos' => $colorojos,
                    'idioma' => $idioma,
                    'servicios' => $servicios_str,
                ),
                array(
                    'id' => $id
                )
            );}
        } else {
            // Si el usuario no ha llenado el formulario previamente, se insertan los datos en la base de datos
            $wpdb->insert(
                $table_name,
                array(
                    'imagenes' => $imagenes,
                    'nombre' => $nombre,
                    'url' => home_url('/destacados/' . sanitize_title(str_replace(' ', '-', $nombre)).'-'.$user_id),
                    'user_id' => $user_id,
                    'fecha_actualizacion'=> current_time('mysql'),
                    'frecuencia' => $frecuencia,
                    'numero_contacto' => $numero_contacto,
                    'whatsapp' => $whatsapp,
                    'edad' => $edad,
                    'ubicacion' => $ubicacion,
                    'descripcion' => $descripcion,
                    'disponibilidad' => $disponibilidad_str,
                    'peso' => $peso,
                    'altura' => $altura,
                    'nacionalidad' => $nacionalidad,
                    'piel' => $piel,
                    'largocabello' => $largocabello,
                    'colorcabello' => $colorcabello,
                    'tamañopecho' => $tamañopecho,
                    'complexion' => $complexion,
                    'fumadora' => $fumadora,
                    'tarifas' => $tarifas,
                    'moneda' => $moneda,
                    'pechooperado' => $pechooperado,
                    'orientacion' => $orientacion,
                    'colorojos' => $colorojos,
                    'idioma' => $idioma,
                    'servicios' => $servicios_str,
                )
            );
            $anuncio_id = $wpdb->insert_id;
        // Crea la página para el nuevo anuncio
$post_title = $nombre;
$post_name = sanitize_title(str_replace(' ', '-', $nombre)).'-'.$user_id;
$post_content = '';

  $post_content = '<div class="superior"><div class="superior-right"><div class="datos"><h1>' . $nombre . '</h1>';
                    if ($ubicacion) {
                        $post_content .= '<p><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" version="1.1" id="Capa_1" width="800px" height="800px" viewBox="0 0 395.71 395.71" xml:space="preserve">
<g>
  <path d="M197.849,0C122.131,0,60.531,61.609,60.531,137.329c0,72.887,124.591,243.177,129.896,250.388l4.951,6.738   c0.579,0.792,1.501,1.255,2.471,1.255c0.985,0,1.901-0.463,2.486-1.255l4.948-6.738c5.308-7.211,129.896-177.501,129.896-250.388   C335.179,61.609,273.569,0,197.849,0z M197.849,88.138c27.13,0,49.191,22.062,49.191,49.191c0,27.115-22.062,49.191-49.191,49.191   c-27.114,0-49.191-22.076-49.191-49.191C148.658,110.2,170.734,88.138,197.849,88.138z"/>
</g>
</svg> ' . $ubicacion . '</p>';
                    }
                    if ($nacionalidad) {
                        $post_content .= '<p>Nacionalidad: ' . $nacionalidad . '</p>';
                    }
                    // Asumiendo que las variables $tarifas y $moneda ya tienen valores asignados
if ($tarifas && $moneda) {
    $post_content .= '<p><span class="precio">' . $tarifas . ' ' . strtoupper($moneda) . '/h</span></p></div>';
}
                    if ($edad) {
                        $post_content .= '<div class="datos-aba"><p><span>Edad:</span> ' . $edad . '</p>';
                    }
                    if ($altura) {
                        $post_content .= '<p><span>Altura:</span> ' . $altura . '</p>';
                    }
                    if ($peso) {
                        $post_content .= '<p><span>Peso:</span> ' . $peso . '</p>';
                    }
                    if ($complexion) {
                        $post_content .= '<p><span>Complexion:</span> ' . $complexion . '</p>';
                    }
                    if ($piel) {
                        $post_content .= '<p><span>Piel:</span> ' . $piel . '</p>';
                    }
                    if ($colorojos) {
                        $post_content .= '<p><span>Color de ojos:</span> ' . $colorojos . '</p>';
                    }
                    if ($largocabello) {
                        $post_content .= '<p><span>Largo del Cabello:</span> ' . $largocabello . '</p>';
                    }
                    if ($colorcabello) {
                        $post_content .= '<p><span>Color del cabello:</span> ' . $colorcabello . '</p>';
                    }
                    if ($tamañopecho) {
                        $post_content .= '<p><span>Tamaño de Pechos:</span> ' . $tamañopecho . '</p>';
                    }      
                    if ($pechooperado) {
                        $post_content .= '<p><span>Pecho Operado:</span> ' . $pechooperado . '</p>';
                    }
                    if ($orientacion) {
                        $post_content .= '<p><span>Orientación Sexual:</span> ' . $orientacion . '</p>';
                    }
                    if ($fumadora) {
                        $post_content .= '<p><span>Fumadora:</span> ' . $fumadora . '</p>';
                    }
                    if ($idioma) {
                        $post_content .= '<p><span>Idioma:</span> ' . $idioma . '</p>';
                    }
                                    $post_content .= '</div></div></div> ';
                     if (isset($imagenes) && !empty($imagenes)) {
    $imagenes_urls = explode(',', $imagenes);

    // Obtener la primera imagen
    $primera_imagen = trim($imagenes_urls[0]);

    // Asegúrate de que la URL de la imagen es válida antes de agregarla al contenido del post
    if (!empty($primera_imagen)) {
        $post_content .= '<div class="img"><img width="30%" src="' . esc_url($primera_imagen) . '"></div></div>';
        // Agrega el resto de las imágenes al contenido
    } else {
        $post_content .= '<div class="img"><p>No hay imagen disponible</p></div></div>';
    }
}
                      $post_content .= '</div></div><div class="contact">';
if ($numero_contacto) {
$telefono_svg_url = plugins_url('images/telefono.svg', __FILE__);
    $post_content .= '<a href="tel:' . $numero_contacto . '" class="telefono"><img src="' . $telefono_svg_url . '" alt="Teléfono">' . $numero_contacto . '</a>';
}
if ($whatsapp) {
  $blogName = get_bloginfo('name');
    $encodedBlogName = urlencode($blogName);
$whatsapp_svg_url = plugins_url('images/whatsapp.svg', __FILE__);
    $post_content .= '<a href="https://wa.me/' . $whatsapp . '?text=Hola%2C%20acabo%20de%20ver%20tu%20anuncio%20en%20' . $encodedBlogName . '%20me%20gustar%C3%ADa%20quedar%20contigo." class="whatsapp"><img src="' . $whatsapp_svg_url . '" alt="WhatsApp">' . $whatsapp . '</a>';
}
$post_content .= '</div>';
                   $post_content .= ' </div><div class="img-gallery">';
                    // Agregar las imágenes del anuncio destacado a la página
                    if ($imagenes) {
                        $imagenes_urls = explode(',', $imagenes);
                        foreach ($imagenes_urls as $imagen_url) {
                             $post_content .= '<div class="img-gallery-single"><img width="30%" src="' . $imagen_url . '"></div>';
                        }
                    }
                      if ($descripcion) {
                        $post_content .= '</div><div class="descripcion"><h3 class="h3">Sobre Mi:</h3><p>' . $descripcion . '</p></div>';
                    }
                    if (is_array($disponibilidad) && !empty($disponibilidad)) {
    $post_content .= '<div class="diservi"><div class="disp-lista"><p>Disponibilidad:</p>';
    $post_content .= '<ul>';
    foreach ($disponibilidad as $disponible) {
        $post_content .= '<li>' . $disponible . '</li>';
    }
    $post_content .= '</ul></div>';
}
if (is_array($servicios) && !empty($servicios)) {
    $post_content .= '<div class="servicios-lista"><p>Servicios:</p>';
    $post_content .= '<ul>';
    foreach ($servicios as $servicio) {
        $post_content .= '<li>' . $servicio . '</li>';
    }
    $post_content .= '</ul></div></div>';
}

  $post_content .= '</div><div class="contact">';
if ($numero_contacto) {
$telefono_svg_url = plugins_url('images/telefono.svg', __FILE__);
    $post_content .= '<a href="tel:' . $numero_contacto . '" class="telefono"><img src="' . $telefono_svg_url . '" alt="Teléfono">' . $numero_contacto . '</a>';
}
if ($whatsapp) {
  $blogName = get_bloginfo('name');
    $encodedBlogName = urlencode($blogName);
$whatsapp_svg_url = plugins_url('images/whatsapp.svg', __FILE__);
    $post_content .= '<a href="https://wa.me/' . $whatsapp . '?text=Hola%2C%20acabo%20de%20ver%20tu%20anuncio%20en%20' . $encodedBlogName . '%20me%20gustar%C3%ADa%20quedar%20contigo." class="whatsapp"><img src="' . $whatsapp_svg_url . '" alt="WhatsApp">' . $whatsapp . '</a>';
}
$post_content .= ' </div><div class="img-gallery">';

                    // Agregar las imágenes del anuncio destacado a la página
                    if ($imagenes) {
                        $imagenes_urls = explode(',', $imagenes);
                        foreach ($imagenes_urls as $imagen_url) {
                             $post_content .= '<div class="img-gallery-single"><img width="30%" src="' . $imagen_url . '"></div>';
                        }
                    }
                    $post_content .= '</div><div class="descripcion">';
                      if ($descripcion) {
                        $post_content .= '<h3 class="h3">Sobre Mi:</h3><p>' . $descripcion . '</p>';
                    }
                    $post_content .= '</div><div class="diservi"><div class="disp-lista">';
                    if (is_array($disponibilidad) && !empty($disponibilidad)) {
    $post_content .= '<p>Disponibilidad:</p>';
    $post_content .= '<ul>';
    foreach ($disponibilidad as $disponible) {
        $post_content .= '<li>' . $disponible . '</li>';
    }
    $post_content .= '</ul>';
}
 $post_content .= '</div><div class="servicios-lista">';
if (is_array($servicios) && !empty($servicios)) {
    $post_content .= '<p>Servicios:</p>';
    $post_content .= '<ul>';
    foreach ($servicios as $servicio) {
        $post_content .= '<li>' . $servicio . '</li>';
    }
    $post_content .= '</ul>';
}
 $post_content .= '</div></div><div class="contact">';
if ($numero_contacto) {
$telefono_svg_url = plugins_url('images/telefono.svg', __FILE__);
    $post_content .= '<a href="tel:' . $numero_contacto . '" class="telefono"><img src="' . $telefono_svg_url . '" alt="Teléfono">' . $numero_contacto . '</a>';
}
if ($whatsapp) {
  $blogName = get_bloginfo('name');
    $encodedBlogName = urlencode($blogName);
$whatsapp_svg_url = plugins_url('images/whatsapp.svg', __FILE__);
    $post_content .= '<a href="https://wa.me/' . $whatsapp . '?text=Hola%2C%20acabo%20de%20ver%20tu%20anuncio%20en%20' . $encodedBlogName . '%20me%20gustar%C3%ADa%20quedar%20contigo." class="whatsapp"><img src="' . $whatsapp_svg_url . '" alt="WhatsApp">' . $whatsapp . '</a>';
}
$post_content .= '</div>';
// Crea la página$cat_id = get_cat_ID( 'Ejemplo' );
$cat_id = get_cat_ID( 'Destacados' );
$new_post = array(
    'post_type' => 'post',
    'post_title' => $post_title,
    'post_name' => $post_name,
    'post_content' => $post_content,
    'post_status' => 'publish',
    'post_author' => $user_id,
    'post_category' => array( $cat_id )
);
// Inserta la página en la base de datos y obtiene su ID
$post_id = wp_insert_post($new_post);
// Crea el permalink personalizado
        $permalink = home_url('/' . $post_name );
        // Actualiza el permalink de la página
        wp_update_post(
            array(
                'ID' => $post_id,
                'post_name' => $post_name,
                'guid' => $permalink,
                'post_content' => $post_content,
            )
        );
// Asigna la página al nuevo anuncio
update_post_meta($post_id, 'anuncio_destacado', $anuncio_id);
        }
        if ($wpdb->last_error) {
            echo '<div class="error">No se pudieron guardar los datos en la base de datos. Por favor, inténtalo de nuevo.</div>';
        } else {
            echo '<div class="success">Los datos se han guardado correctamente.</div>';
        }
    }
}
add_action( 'init', 'destacados_form_handler' );

function add_minutely_cron_interval( $schedules ) {

    $schedules['minutely'] = array(

        'interval' => 60, // Cada 60 segundos (1 minuto)

        'display'  => __( 'Cada Minuto' ),

    );

    return $schedules;

}

add_filter( 'cron_schedules', 'add_minutely_cron_interval' );

function delete_anuncio_destacado_on_page_delete( $postid ) {

    if ( $postid ) {

        global $wpdb;

        // Obtener el user ID del autor del post
        $author_id = get_post_field( 'post_author', $postid );

        // Consultar la ubicación del anuncio en la tabla destacados usando el user ID del autor
        $table_name = $wpdb->prefix . 'destacados';
        $ubicacion = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ubicacion FROM {$table_name} WHERE user_id = %d",
                $author_id
            )
        );
        error_log('Valor de ubicacion: ' . $ubicacion);

        // Eliminar registro de la primera tabla "destacados"
        $deleted_rows_1 = $wpdb->delete(
            $table_name,
            array( 'user_id' => $author_id )
        );
        error_log('Filas eliminadas de la primera tabla: ' . $deleted_rows_1);

        // Construir el nombre de la segunda tabla usando la ubicación del anuncio
        $table_name_ = $wpdb->prefix . 'destacados_' . str_replace(' ', '_', strtolower($ubicacion));
        error_log('Nombre de la segunda tabla: ' . $table_name_);

        // Eliminar registro de la segunda tabla "destacados_$ubicacion"
        $deleted_rows_2 = $wpdb->delete(
            $table_name_,
            array( 'user_id' => $author_id )
        );
        error_log('Filas eliminadas de la segunda tabla: ' . $deleted_rows_2);

    }

}
add_action( 'before_delete_post', 'delete_anuncio_destacado_on_page_delete' );

function destacadoss_create_page() {

    // Crear una nueva página

    $destacados_page = array(

        'post_title'    => 'Destacados',

        'post_content'  => '[destacados]',

        'post_status'   => 'publish',

        'post_author'   => 1,

        'post_type'     => 'page'

    );

    wp_insert_post( $destacados_page );

}



// Llamar la función cuando se active el plugin

register_activation_hook( __FILE__, 'destacadoss_create_page' );

/*locacion1*/

function destacados_create_page_sevilla() {

    // Crea una nueva página en WordPress con el título y contenido especificado

    $page = array(

        'post_title'    => 'Destacados Sevilla',

        'post_content'  => '[destacados_sevilla]',

        'post_status'   => 'publish',

        'post_author'   => 1,

        'post_type'     => 'page'

    );

    wp_insert_post( $page );

}

register_activation_hook( __FILE__, 'destacados_create_page_sevilla' );

function destacados_shortcode_sevilla($atts)
{
    global $wpdb;
    // Obtener los parámetros del shortcode
    $params = shortcode_atts(array(
        'per_page' => 3,
    ), $atts);

    // Iniciar la salida
    $output = '';

    // Agregar el contenedor en el que se cargarán los anuncios
    $output .= '<div id="anuncios-container" data-per-page="' . esc_attr($params['per_page']) . '" data-page="1">';

    // Obtener los primeros resultados
    $table_name = $wpdb->prefix . 'destacados_sevilla';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY posicion ASC LIMIT $params[per_page]");

    // Agregar los primeros resultados al contenedor
    if ($results) {
        foreach ($results as $result) {
            $output .= format_destacado_html_sevilla($result);
        }
    } else {
        $output .= 'No se encontraron resultados.';
    }

    $output .= '</div>';

    // Añadir una etiqueta para la carga infinita
    $output .= '<div class="load-more" data-page="1"></div>';

    // Agregar un spinner para mostrar mientras se cargan nuevos anuncios
    $output .= '<div class="spinner" style="display: none;">
                    <img src="' . plugin_dir_url(__FILE__) . '/images/spinner.gif" alt="Loading...">
                </div>';
    // Agregar el código JavaScript para la carga infinita
    $output .= '<script type="text/javascript">
                    jQuery(document).ready(function($) {
                        var isLoading = false;

                        function loadMoreAnuncios() {
                            if (isLoading) return;
                            isLoading = true;

                            var loadMore = $(".load-more");
                            var page = parseInt(loadMore.data("page")) + 1;
                            loadMore.data("page", page);

                            $.ajax({
                                url: "' . admin_url('admin-ajax.php') . '",
                                type: "POST",
                                data: {
                                    action: "load_more_anuncios_sevilla",
                                    per_page: 3,
                                    paged: page
                                },
                                success: function(response) {
                                    if (response) {
                                        $("#anuncios-container").append(response);
                                        isLoading = false;
                                    } else {
                                        // No hay más resultados
                                        isLoading = true;
                                    }
                                },
                                error: function() {
                                    isLoading = false;
                                }
                            });
                        }

                        // Cargar anuncios iniciales
                        loadMoreAnuncios();

                        $(window).scroll(function() {
                            if ($(window).scrollTop() + $(window).height() > $(".load-more").offset().top) {
                                loadMoreAnuncios();
                            }
                        });
                    });
                </script>';

    // Devolver la salida
    return $output;
}
function format_destacado_html_sevilla($result)
{
    $imagenes_array = explode(',', $result->imagenes);
$primera_imagen = array_shift($imagenes_array);
$imagen_html = '<img src="' . $primera_imagen . '" width="100%" height="auto">';

$output = '';
$output .= '<div class="destacado">';
$output .= '<a href="'. $result->url .'" class="destacado">';
$output .= '<div class="float-box">';
$output .= '<span class="escort">Escort Premium</span>';
$output .= '<span class="price">'  . $result->tarifas .' '. $result->moneda .  '/h</span>';
$output .= '</div>';
$output .= '<div class="image-container">' . $imagen_html . '<div class="star"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" aria-hidden="true" class="favourite-icon-favourite"><!--! Font Awesome Free 6.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"></path></svg></div></div>';
$output .= '<div class="details">';
$output .= '<h2>' . $result->nombre . '</h2>';
$output .= '<span>' . $result->edad . ' Años</span>';
$output .= '<span class="ubicacion"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" version="1.1" id="Capa_1" width="800px" height="800px" viewBox="0 0 395.71 395.71" xml:space="preserve">
<g>
  <path d="M197.849,0C122.131,0,60.531,61.609,60.531,137.329c0,72.887,124.591,243.177,129.896,250.388l4.951,6.738   c0.579,0.792,1.501,1.255,2.471,1.255c0.985,0,1.901-0.463,2.486-1.255l4.948-6.738c5.308-7.211,129.896-177.501,129.896-250.388   C335.179,61.609,273.569,0,197.849,0z M197.849,88.138c27.13,0,49.191,22.062,49.191,49.191c0,27.115-22.062,49.191-49.191,49.191   c-27.114,0-49.191-22.076-49.191-49.191C148.658,110.2,170.734,88.138,197.849,88.138z"/>
</g>
</svg>' . $result->ubicacion . '</span>';
$output .= '</div>';
$output .= '</a>';
$output .= '</div>';

return $output;
}
function load_more_anuncios_sevilla()
{
    global $wpdb;

    $per_page = intval($_POST['per_page']);
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;

    $offset = ($paged - 1) * $per_page;

    $table_name = $wpdb->prefix . 'destacados_sevilla';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY posicion ASC LIMIT $per_page OFFSET $offset");

    $output = '';
    if ($results) {
        foreach ($results as $result) {
            $output .= format_destacado_html_sevilla($result);
        }
    }

    echo $output;
    wp_die();
}


add_shortcode('destacados_sevilla', 'destacados_shortcode_sevilla');
add_action('wp_ajax_load_more_anuncios_sevilla', 'load_more_anuncios_sevilla');
add_action('wp_ajax_nopriv_load_more_anuncios_sevilla', 'load_more_anuncios_sevilla');
function enqueue_infinite_scroll_assets()
{
    wp_enqueue_script('infinite-scroll-js', plugin_dir_url(__FILE__) . 'js/infinite-scroll.min.js', array('jquery'), '1.0.0', true);
    wp_localize_script('infinite-scroll-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'enqueue_infinite_scroll_assets');
 // Agregar cronjob para actualizar la fecha de actualización cada minuto

function destacados_cron_activation_sevilla() {

    if (! wp_next_scheduled ( 'destacados_cronjob_sevilla' )) {
        wp_schedule_event( time(), 'minutely', 'destacados_cronjob_sevilla' );
    }
}
register_activation_hook( __FILE__, 'destacados_cron_activation_sevilla');

// Función para actualizar la fecha de actualización de los anuncios

function destacados_update_date_sevilla() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'destacados_sevilla';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY posicion DESC");
    $current_date = current_time('mysql');

    // Iterar sobre los resultados y verificar la frecuencia de actualización
    foreach ($results as $key => $result) {
        $frecuencia = $result->frecuencia;
        $last_updated = $result->fecha_actualizacion;
        $user_id = $result->user_id;

        $credits = $wpdb->get_var($wpdb->prepare("SELECT credits FROM {$wpdb->prefix}users WHERE ID = %d", $user_id));

        // Verificar si el usuario tiene créditos para actualizar el anuncio
        if ($credits <= 0) {
    $wpdb->update($table_name, array('frecuencia' => 0), array('user_id' => $user_id));
    $destacados_table_name = $wpdb->prefix . 'destacados';
    $wpdb->update($destacados_table_name, array('frecuencia' => 0), array('user_id' => $user_id));
    continue;
}

        $diff = strtotime($current_date) - strtotime($last_updated);
        $diff_in_minutes = round($diff / 60);

        // Si la diferencia de tiempo es mayor o igual a la frecuencia, actualizar la fecha de actualización y créditos del usuario
        if ($diff_in_minutes >= $frecuencia && $frecuencia > 0) {
            $new_date = $current_date;

            if ($credits >= 1 && $result->posicion != 1) {
                $wpdb->update($table_name, array('fecha_actualizacion' => $new_date), array('user_id' => $user_id));
                $credits--;
                $wpdb->update($wpdb->prefix . 'users', array('credits' => $credits), array('ID' => $user_id));
            }
        }
    }

    // Eliminar las posiciones existentes
    $wpdb->query("UPDATE $table_name SET posicion = NULL");

    // Obtener los resultados actualizados y ordenarlos por fecha de actualización
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY fecha_actualizacion DESC, posicion ASC");

    // Iterar sobre los resultados y actualizar la posición de cada anuncio
    $position = 1;
    foreach ($results as $result) {
        $wpdb->update($table_name, array('posicion' => $position), array('id' => $result->id));
        $position++;
    }
}
function destacados_cronjob_sevilla() {
    destacados_update_date_sevilla();
}
add_action('destacados_cronjob_sevilla', 'destacados_cronjob_sevilla');

/*Cerrar locacion 1*/

/*Locacion2*/

function destacados_create_page_alcala_de_guadaira() {

    // Crea una nueva página en WordPress con el título y contenido especificado

    $page = array(

        'post_title'    => 'Destacados alcala_de_guadaira',

        'post_content'  => '[destacados_alcala_de_guadaira]',

        'post_status'   => 'publish',

        'post_author'   => 1,

        'post_type'     => 'page'

    );

    wp_insert_post( $page );

}

register_activation_hook( __FILE__, 'destacados_create_page_alcala_de_guadaira' );

function destacados_shortcode_alcala_de_guadaira($atts)
{
    global $wpdb;
    // Obtener los parámetros del shortcode
    $params = shortcode_atts(array(
        'per_page' => 3,
    ), $atts);

    // Iniciar la salida
    $output = '';

    // Agregar el contenedor en el que se cargarán los anuncios
    $output .= '<div id="anuncios-container" data-per-page="' . esc_attr($params['per_page']) . '" data-page="1">';

    // Obtener los primeros resultados
    $table_name = $wpdb->prefix . 'destacados_alcala_de_guadaira';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY posicion ASC LIMIT $params[per_page]");

    // Agregar los primeros resultados al contenedor
    if ($results) {
        foreach ($results as $result) {
            $output .= format_destacado_html_alcala_de_guadaira($result);
        }
    } else {
        $output .= 'No se encontraron resultados.';
    }

    $output .= '</div>';

    // Añadir una etiqueta para la carga infinita
    $output .= '<div class="load-more" data-page="1"></div>';
    // Agregar el código JavaScript para la carga infinita
    $output .= '<script type="text/javascript">
                    jQuery(document).ready(function($) {
                        var isLoading = false;

                        function loadMoreAnuncios() {
                            if (isLoading) return;
                            isLoading = true;

                            var loadMore = $(".load-more");
                            var page = parseInt(loadMore.data("page")) + 1;
                            loadMore.data("page", page);

                            $.ajax({
                                url: "' . admin_url('admin-ajax.php') . '",
                                type: "POST",
                                data: {
                                    action: "load_more_anuncios_alcala_de_guadaira",
                                    per_page: 3,
                                    paged: page
                                },
                                success: function(response) {
                                    if (response) {
                                        $("#anuncios-container").append(response);
                                        isLoading = false;
                                    } else {
                                        // No hay más resultados
                                        isLoading = true;
                                    }
                                },
                                error: function() {
                                    isLoading = false;
                                }
                            });
                        }

                        // Cargar anuncios iniciales
                        loadMoreAnuncios();

                        $(window).scroll(function() {
                            if ($(window).scrollTop() + $(window).height() > $(".load-more").offset().top) {
                                loadMoreAnuncios();
                            }
                        });
                    });
                </script>';

    // Devolver la salida
    return $output;
}
function format_destacado_html_alcala_de_guadaira($result)
{
    $imagenes_array = explode(',', $result->imagenes);
$primera_imagen = array_shift($imagenes_array);
$imagen_html = '<img src="' . $primera_imagen . '" width="100%" height="auto">';

$output = '';
$output .= '<div class="destacado">';
$output .= '<a href="'. $result->url .'" class="destacado">';
$output .= '<div class="float-box">';
$output .= '<span class="escort">Escort Premium</span>';
$output .= '<span class="price">'  . $result->tarifas .' '. $result->moneda .  '/h</span>';
$output .= '</div>';
$output .= '<div class="image-container">' . $imagen_html . '<div class="star"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" aria-hidden="true" class="favourite-icon-favourite"><!--! Font Awesome Free 6.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"></path></svg></div></div>';
$output .= '<div class="details">';
$output .= '<h2>' . $result->nombre . '</h2>';
$output .= '<span>' . $result->edad . ' Años</span>';
$output .= '<span class="ubicacion"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" version="1.1" id="Capa_1" width="800px" height="800px" viewBox="0 0 395.71 395.71" xml:space="preserve">
<g>
  <path d="M197.849,0C122.131,0,60.531,61.609,60.531,137.329c0,72.887,124.591,243.177,129.896,250.388l4.951,6.738   c0.579,0.792,1.501,1.255,2.471,1.255c0.985,0,1.901-0.463,2.486-1.255l4.948-6.738c5.308-7.211,129.896-177.501,129.896-250.388   C335.179,61.609,273.569,0,197.849,0z M197.849,88.138c27.13,0,49.191,22.062,49.191,49.191c0,27.115-22.062,49.191-49.191,49.191   c-27.114,0-49.191-22.076-49.191-49.191C148.658,110.2,170.734,88.138,197.849,88.138z"/>
</g>
</svg>' . $result->ubicacion . '</span>';
$output .= '</div>';
$output .= '</a>';
$output .= '</div>';

return $output;
}

function load_more_anuncios_alcala_de_guadaira()
{
    global $wpdb;

    $per_page = intval($_POST['per_page']);
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;

    $offset = ($paged - 1) * $per_page;

    $table_name = $wpdb->prefix . 'destacados_alcala_de_guadaira';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY posicion ASC LIMIT $per_page OFFSET $offset");

    $output = '';
    if ($results) {
        foreach ($results as $result) {
            $output .= format_destacado_html_alcala_de_guadaira($result);
        }
    }

    echo $output;
    wp_die();
}


add_shortcode('destacados_alcala_de_guadaira', 'destacados_shortcode_alcala_de_guadaira');
add_action('wp_ajax_load_more_anuncios_alcala_de_guadaira', 'load_more_anuncios_alcala_de_guadaira');
add_action('wp_ajax_nopriv_load_more_anuncios_alcala_de_guadaira', 'load_more_anuncios_alcala_de_guadaira');
 // Agregar cronjob para actualizar la fecha de actualización cada minuto

function destacados_cron_activation_alcala_de_guadaira() {

    if (! wp_next_scheduled ( 'destacados_cronjob_alcala_de_guadaira' )) {
        wp_schedule_event( time(), 'minutely', 'destacados_cronjob_alcala_de_guadaira' );
    }
}
register_activation_hook( __FILE__, 'destacados_cron_activation_alcala_de_guadaira');

// Función para actualizar la fecha de actualización de los anuncios

function destacados_update_date_alcala_de_guadaira() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'destacados_alcala_de_guadaira';
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY posicion DESC");
    $current_date = current_time('mysql');

    // Iterar sobre los resultados y verificar la frecuencia de actualización
    foreach ($results as $key => $result) {
        $frecuencia = $result->frecuencia;
        $last_updated = $result->fecha_actualizacion;
        $user_id = $result->user_id;

        $credits = $wpdb->get_var($wpdb->prepare("SELECT credits FROM {$wpdb->prefix}users WHERE ID = %d", $user_id));

        // Verificar si el usuario tiene créditos para actualizar el anuncio
        if ($credits <= 0) {
    $wpdb->update($table_name, array('frecuencia' => 0), array('user_id' => $user_id));
    $destacados_table_name = $wpdb->prefix . 'destacados';
    $wpdb->update($destacados_table_name, array('frecuencia' => 0), array('user_id' => $user_id));
    continue;
}

        $diff = strtotime($current_date) - strtotime($last_updated);
        $diff_in_minutes = round($diff / 60);

        // Si la diferencia de tiempo es mayor o igual a la frecuencia, actualizar la fecha de actualización y créditos del usuario
        if ($diff_in_minutes >= $frecuencia && $frecuencia > 0) {
            $new_date = $current_date;

            if ($credits >= 1 && $result->posicion != 1) {
                $wpdb->update($table_name, array('fecha_actualizacion' => $new_date), array('user_id' => $user_id));
                $credits--;
                $wpdb->update($wpdb->prefix . 'users', array('credits' => $credits), array('ID' => $user_id));
            }
        }
    }

    // Eliminar las posiciones existentes
    $wpdb->query("UPDATE $table_name SET posicion = NULL");

    // Obtener los resultados actualizados y ordenarlos por fecha de actualización
    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY fecha_actualizacion DESC, posicion ASC");

    // Iterar sobre los resultados y actualizar la posición de cada anuncio
    $position = 1;
    foreach ($results as $result) {
        $wpdb->update($table_name, array('posicion' => $position), array('id' => $result->id));
        $position++;
    }
}
function destacados_cronjob_alcala_de_guadaira() {

    destacados_update_date_alcala_de_guadaira();

}
add_action('destacados_cronjob_alcala_de_guadaira', 'destacados_cronjob_alcala_de_guadaira');
/*Cerrar locacion 2*/

define('WP_CRON_LOCK_TIMEOUT', 5);

define('DISABLE_WP_CRON', true);
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

function add_custom_class_to_destacados_body($classes) {
    // Comprueba si la página actual es una página creada por el código de anuncios destacados
    global $post;
    if ($post && 'post' === $post->post_type && false !== strpos($post->post_content, '<p>Servicios:')) {
        // Agrega la clase personalizada al array de clases del body
        $custom_class = 'destacados';
        $classes[] = $custom_class;
    }

    return $classes;
}
add_filter('body_class', 'add_custom_class_to_destacados_body');
