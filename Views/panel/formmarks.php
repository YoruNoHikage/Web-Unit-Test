<div class="col-md-8 col-md-push-2">

    <form class="form-horizontal" action="index.php?action=addtests&id=<?php echo $project->getId() ?>" method="post" role="form">
        <?php
            foreach($filesToProcess as $file)
            {
        ?>
                <h3>
                <?php 
                    echo $file['class'];
                    if($file['reprocess']) {echo ' (édition)';}
                    else { echo ' (ajout)'; }
                ?>
                </h3>
                <div class="form-group">
                    <?php
                        $subtests = $file['subtests'];
                        foreach ($subtests as $subtest)
                        {
                            $fullname = $subtest['name'] . ':' . $file['class'] . ':' . $project->getId();
                    ?>
                            <label for="<?php echo $fullname  ?>" class="col-sm-6 control-label"><?php echo $subtest['name']; ?></label>
                            <div class="col-sm-2">
                                <select name="<?php echo $fullname  ?>" id="<?php echo $fullname  ?>" class="form-control">
                                    <?php 
                                        for($i = 1 ; $i <= 5 ; $i++)
                                        {
                                    ?>
                                            <option <?php if($i == $subtest['value']) { echo 'selected'; } ?>
                                                    value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php
                                        }
                                    ?>
                                </select>
                            </div>
                            
                            <input type="hidden" name="reprocess" value="<?php echo $file['reprocess']; ?>">
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