<html>
    <body>
        <script>
        	var form = document.createElement("form");
        	form.setAttribute("method", "POST");
        	form.setAttribute("action", "<?php echo $startPayUrl ?>");
        	form.setAttribute("target", "_self");

            var hiddenField = document.createElement("input");
        	hiddenField.setAttribute("name", "RefId");
        	hiddenField.setAttribute("value", "<?php echo $refId ?>");

            var hiddenField2 = document.createElement("input");
        	hiddenField2.setAttribute("name", "MobileNo");
        	hiddenField2.setAttribute("value", "<?php echo $mobile ?>");

            form.appendChild(hiddenField);
            form.appendChild(hiddenField2);

        	document.body.appendChild(form);
        	form.submit();
        	document.body.removeChild(form);
        </script>
    </body>
</html>
