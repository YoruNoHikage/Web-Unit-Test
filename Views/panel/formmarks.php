<div class="col-md-8 col-md-push-2">

    <form class="form-horizontal" action="index.php?action=createproject" method="post" role="form">
        <?php
            foreach($filesToProcess as $fileToProcess)
            {
        ?>
                <h3><?php echo $fileToProcess['class']; ?></h3>
                <div class="form-group">
                    <?php
                        $subtests = $fileToProcess['subtests'];
                        foreach ($subtests as $subtest)
                        {
                    ?>
                            <label for="password" class="col-sm-6 control-label"><?php echo $subtest; ?></label>
                            <div class="col-sm-2">
                                <select class="form-control">
                                    <?php 
                                        for($i = 1 ; $i <= 5 ; $i++)
                                        {
                                    ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php
                                        }
                                    ?>
                                </select>
                            </div>
                    <?php
                        }
                    ?>
                </div>
        <?php
            }
        ?>

        <div class="form-group">
            <div class="col-sm-offset-6 col-sm-9">
                <button type="submit" class="btn btn-default">Valider</button>
            </div>
        </div>
    </form>

</div>