import subprocess
import traceback
from genericpath import exists
from os.path import join, splitext, basename
from typing import List

from config import Config
from checkout import checkout_parent, reset_to_revision
from datareader import on_all_data_do
from utils.data_util import extract_project_name_from_file_path
from utils.io import safe_open
from utils.logger import log_error

CATCH_ERRORS = True  # only used for testing


def run_detector_on_all_data() -> List[None]:
    on_all_data_do(run_detector)


def run_detector(file: str, misuse: dict) -> None:
    """
    Runs the misuse detector on the given misuse
    :param file: The file containing the misuse information
    :param misuse: The dictionary containing the misuse information
    :rtype: None
    :return: Nothing
    """
    try:
        result_dir = join(Config.RESULTS_PATH, Config.MISUSE_DETECTOR, splitext(basename(file))[0])

        fix = misuse["fix"]
        repository = fix["repository"]

        base_dir = Config.CHECKOUT_DIR
        project_name = extract_project_name_from_file_path(file)
        checkout_dir = join(base_dir, project_name)

        if not exists(checkout_dir):
            checkout_parent(repository["type"], repository["url"], fix.get('revision', ""), checkout_dir)
        else:
            reset_to_revision(repository["type"], checkout_dir, fix.get('revision', ""))

        print("Running \'{}\'; Results in \'{}\'...".format(Config.MISUSE_DETECTOR, result_dir))

        with safe_open(join(result_dir, Config.LOG_DETECTOR_OUT), 'w+') as out_log:
            with safe_open(join(result_dir, Config.LOG_DETECTOR_ERROR), 'w+') as error_log:
                try:
                    absolute_misuse_detector_path = join(Config.MISUSE_DETECTOR_PATH, Config.MISUSE_DETECTOR,
                                                         Config.MISUSE_DETECTOR + '.jar')

                    subprocess.call(["java", "-jar", absolute_misuse_detector_path, checkout_dir, result_dir],
                                    bufsize=1, stdout=out_log, stderr=error_log, timeout=Config.TIMEOUT)
                except subprocess.TimeoutExpired:
                    print("Timeout: {}".format(file))
                    Config.BLACK_LIST.append(file)
                    return

    except (KeyboardInterrupt, SystemExit):
        raise
    except:
        if not CATCH_ERRORS:
            raise

        exception_string = traceback.format_exc()
        print(exception_string)
        log_error("Error: {} in {}".format(exception_string, file))


